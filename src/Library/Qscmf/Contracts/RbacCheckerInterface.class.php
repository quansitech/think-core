<?php
namespace Qscmf\Contracts;

/**
 * RBAC 权限决策器接口。
 *
 * 抽象出 QsController::_initialize() 中对 QsRbac 静态方法的调用，使权限检查
 * 可通过 DI 容器替换，从而在测试中 mock。
 *
 * 默认实现 \Qscmf\Core\RbacChecker 包装现有的 QsRbac 静态调用，生产环境行为不变；
 * 测试中可绑定一个返回固定值的替身到容器：
 *   app()->instance(RbacCheckerInterface::class, $mockDouble);
 */
interface RbacCheckerInterface
{
    /**
     * 当前请求是否通过权限决策（对应 QsRbac::AccessDecision）。
     *
     * @return bool
     */
    public function accessDecision(): bool;

    /**
     * 指定用户是否拥有指定节点的访问权限（对应 QsRbac::checkAccessNodeId）。
     *
     * @param mixed $authId 用户认证标识
     * @param int   $nodeId 节点 ID
     * @return bool
     */
    public function checkAccessNodeId($authId, int $nodeId): bool;
}
