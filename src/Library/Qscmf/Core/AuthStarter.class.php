<?php

namespace Qscmf\Core;

use Qscmf\Contracts\RbacCheckerInterface;
use Qscmf\Core\RbacChecker;
use Think\Hook;

/**
 * 鉴权协作者：把原 QsController::_initialize 里"无 $this 依赖"的鉴权三步抽出来，
 * 成为一个可经容器注入、可 mock 的独立类。
 *
 * 三步（顺序固定，由 boot() 编排）：
 *   1. resetRbac()  —— 多用户表配置切换（原 _resetRbac，QsController L276-286）
 *   2. verifyLogin() —— 触发登录态校验（原 L133 Hook::listen('verify_login_user')）
 *   3. authorize()   —— RBAC 访问决策（原 L183-185，不过则 E(l('no_auth'))）
 *
 * 为什么独立成类：
 * - 原 _initialize 是构造钩子，鉴权逻辑内联其中，无法单独 mock 或跳过；
 * - 抽成协作者后，测试可通过 app()->instance(AuthStarter::class, $mock) 整体替换，
 *   也可只替换内部的 RbacCheckerInterface；
 * - 被测的纯 DB 逻辑方法不再受构造期鉴权副作用牵连（测试时 bindControllerWithoutInit
 *   跳过 _initialize，本协作者不会执行）。
 *
 * 为什么不经全局 Hook（如 action_begin）：全局标签会对所有控制器触发，包括直接继承
 * Think\Controller 的 Api/Public 开放接口，造成 API 性能损耗与语义错误。本协作者仅由
 * QsController::_initialize 显式调用，继承基类的接口完全不涉及。
 *
 * 中止机制：沿用 E() 抛 Think\Exception（原有机制，App 错误处理捕获），不引入新机制。
 */
class AuthStarter
{
    /**
     * @var RbacCheckerInterface|null 懒加载的 RBAC 决策器（可 mock）
     */
    private $rbac = null;

    /**
     * 鉴权编排：顺序保证 reset → verify → authorize。
     *
     * resetRbac 必须先于 authorize：accessDecision 内部读 C('RBAC_USER_TABLE')，
     * 该配置由 resetRbac 按多用户体系动态设置。
     */
    public function boot(): void
    {
        $this->resetRbac();
        $this->verifyLogin();
        $this->authorize();
    }

    /**
     * 根据用户配置重置 RBAC 用户表和用户与用户组关联表。
     *
     * 复刻原 QsController::_resetRbac（L276-286）。支持多用户体系：当 session 中存在
     * 特定标志（如前台会员登录），切换 USER_AUTH_MODEL / RBAC_USER_TABLE 配置项，
     * 使后续 RBAC 校验查正确的表。
     */
    public function resetRbac(): void
    {
        $inject_rbac_arr = C('INJECT_RBAC');
        if (!empty($inject_rbac_arr)) {
            array_map(function ($str) {
                if (session("?{$str['key']}")) {
                    C('USER_AUTH_MODEL', $str['user'], 'User');
                    C('RBAC_USER_TABLE', $str['role_user'], 'qs_role_user');
                }
            }, $inject_rbac_arr);
        }
    }

    /**
     * 触发登录态校验。
     *
     * 复刻原 _initialize L133。verify_login_user 标签绑定的 VerifyUserBehavior 会
     * 查用户状态，非正常状态则 E('用户状态异常')。
     */
    public function verifyLogin(): void
    {
        Hook::listen('verify_login_user');
    }

    /**
     * RBAC 访问决策。
     *
     * 复刻原 _initialize L183-185。经容器解析决策器（可 mock），不过权限则抛异常中止。
     */
    public function authorize(): void
    {
        if (!$this->resolveRbac()->accessDecision()) {
            E(l('no_auth'));
        }
    }

    /**
     * 从 DI 容器解析 RBAC 决策器（懒加载）。
     *
     * 复刻 QsController::resolveRbac（L42-57）的逻辑：优先容器解析（测试可
     * app()->instance(RbacCheckerInterface::class, $mock) 替换），解析失败回退默认实现，
     * 容器不可用时直接 new。保证生产环境行为不变。
     */
    private function resolveRbac(): RbacCheckerInterface
    {
        if ($this->rbac === null) {
            $container = qs_container();
            if ($container !== null) {
                try {
                    $this->rbac = $container->make(RbacCheckerInterface::class);
                } catch (\Throwable $e) {
                    $this->rbac = new RbacChecker();
                }
            } else {
                $this->rbac = new RbacChecker();
            }
        }
        return $this->rbac;
    }
}
