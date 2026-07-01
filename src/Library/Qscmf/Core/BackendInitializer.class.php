<?php

namespace Qscmf\Core;

use App\Models\Menu;
use Behavior\HeadCssBehavior;
use Behavior\HeadJsBehavior;
use Behavior\InjectHeadBehavior;
use Gy_Library\DBCont;
use Illuminate\Database\Capsule\Manager as Capsule;
use Qscmf\Contracts\RbacCheckerInterface;
use Think\Hook;

/**
 * 后台初始化协作者：承载原 QsController::_initialize 的全部后台初始化逻辑。
 *
 * 职责（由 initialize() 编排）：
 *   1. 鉴权三步（经 AuthStarter->boot()：resetRbac → verifyLogin → authorize）
 *   2. 后台模块特有：注册资源 Hook、加载菜单、RBAC 节点过滤、写视图变量、
 *      触发 handleLayoutProps（Inertia layoutProps 注入）
 *
 * 为什么独立成类：
 * - 原 _initialize 是构造钩子，全部逻辑内联控制器，无法单独 mock 或跳过；
 * - 抽成协作者后，测试经容器绑定 mock 整个类即可消除 _initialize 全部副作用
 *   （含全局函数 C/session/Hook，因 mock 后不可达）；
 * - 被测的纯 DB 逻辑方法不再受构造期初始化副作用牵连。
 *
 * 为什么不经全局 Hook（如 action_begin）：全局标签会对所有控制器触发，包括直接继承
 * Think\Controller 的 Api/Public 开放接口，造成 API 性能损耗与语义错误。本协作者仅由
 * QsController::_initialize 显式调用，继承基类的接口完全不涉及。
 *
 * handleLayoutProps（Inertia layoutProps 注入）：QsController 暴露 public 入口
 * handleLayoutProps()（内部委托 trait 的 protected doHandleLayoutProps），initialize()
 * 经控制器引用调用，读写视图变量共享同一 view。
 *
 * 视图数据写入：通过控制器引用的 __set（public 魔术方法）触发 assign，天然共享
 * 控制器的 $this->view 单例，无需额外注入 View。
 */
class BackendInitializer
{
    /**
     * @var RbacCheckerInterface 节点级 RBAC 决策器（可 mock）
     */
    private RbacCheckerInterface $rbac;

    /**
     * @var AuthStarter|null 鉴权协作者（reset/verify/authorize 三步）
     */
    private ?AuthStarter $authStarter;

    public function __construct(RbacCheckerInterface $rbac, ?AuthStarter $authStarter = null)
    {
        $this->rbac = $rbac;
        $this->authStarter = $authStarter;
    }

    /**
     * 后台初始化入口：由 QsController::_initialize 调用，传入控制器引用。
     *
     * 编排顺序：鉴权 → 后台模块特有逻辑 → layoutProps。
     * 鉴权三步（reset→verify→authorize）经 AuthStarter->boot() 收口，顺序由 boot() 保证。
     * verifyLogin 必须在后台菜单加载前（原 _initialize L175 位置），authorize 在最后。
     */
    public function initialize(QsController $controller): void
    {
        // 鉴权三步：resetRbac（所有 QsController 子类执行）→ authorize（访问决策）
        $this->authStarter?->resetRbac();

        $is_backend = in_array(strtolower(MODULE_NAME), (array)C("BACKEND_MODULE"));

        if ($is_backend) {
            $this->registerHooks();
            $this->authStarter?->verifyLogin();
            $this->buildMenu($controller);
        }

        // RBAC 访问决策（不过权限则 E(l('no_auth')) 中止）
        $this->authStarter?->authorize();

        if ($is_backend && C('ANTD_ADMIN_BUILDER_ENABLE')) {
            // QsController 暴露 public handleLayoutProps() 入口，trait 实现委托于其内部
            // （protected doHandleLayoutProps），经控制器引用读写视图变量。
            $controller->handleLayoutProps();
        }
    }

    /**
     * 注册后台资源 Hook（view_filter × 4 + parse_extend × 2）。
     *
     * 复刻原 _initialize L163-172。这些 Hook 为老式 PHP 模板注入 css/js/html 资源。
     */
    private function registerHooks(): void
    {
        Hook::import(['view_filter' => [HeadCssBehavior::class]], true);
        Hook::import(['view_filter' => [HeadJsBehavior::class]], true);
        Hook::import(['view_filter' => [\Behavior\BodyHtmlBehavior::class]], true);
        Hook::import(['view_filter' => [\Behavior\HeaderNavbarRightHtmlBehavior::class]], true);
        Hook::add('parse_extend', InjectHeadBehavior::class);
        Hook::add('parse_extend', \Behavior\InjectBodyBehavior::class);
    }

    /**
     * 加载菜单、RBAC 节点过滤、写入视图变量。
     *
     * 复刻原 _initialize L177-222。通过控制器的 __set 写视图变量（top_menu/
     * current_module/menu_list），与 handleLayoutProps 的 __get 读取共享同一 view。
     *
     * @param QsController $controller 持有 view 单例的控制器引用
     */
    private function buildMenu(QsController $controller): void
    {
        $menu = qs_instantiate(Menu::class);

        $top_menu_list = $menu->getMenuList('top_menu');
        $controller->top_menu = $top_menu_list;
        $controller->current_module = strtolower(MODULE_NAME);

        $top_menu_id = 0;
        foreach ($top_menu_list as $top_menu) {
            if ($top_menu['module'] == strtolower(MODULE_NAME)) {
                $top_menu_id = $top_menu['id'];
                break;
            }
        }

        $menu_list = $menu->getMenuList('backend_menu', $top_menu_id);
        $show_list = [];

        $menu_ids = array_column((array)$menu_list, "id");
        $node_group_with_menu = !empty($menu_ids) ? $this->nodeGroupWithMenu($menu_ids) : [];

        for ($i = 0, $iMax = count((array)$menu_list); $i < $iMax; $i++) {
            $node_list = $node_group_with_menu[$menu_list[$i]['id']] ?? [];

            $show_node_list = [];
            $add_flag = false;
            for ($n = 0, $nMax = count((array)$node_list); $n < $nMax; $n++) {
                $node = $node_list[$n];
                $node_id = $node['id'];
                if ($this->rbac->checkAccessNodeId(session(C('USER_AUTH_KEY')), $node_id)) {
                    $node_list[$n]['url'] = $this->nodeUrl1($node);
                    $show_node_list[] = $node_list[$n];
                    $add_flag = true;
                }
            }
            // 只显示有权限操作的菜单项
            if ($add_flag) {
                $menu_list[$i]['node_list'] = $show_node_list;
                $show_list[] = $menu_list[$i];
            }
        }

        $controller->menu_list = $show_list;
    }

    /**
     * 按 menu_id 分组查询动作级节点。
     *
     * 迁自 QsController::_nodeGroupWithMenu（纯 DB 查询，无 $this 依赖）。
     */
    private function nodeGroupWithMenu(array $menu_ids): array
    {
        $list = Capsule::table(Capsule::raw('(' . buildNodeVSql() . ') as n_v'))
            ->where('status', DBCont::NORMAL_STATUS)
            ->whereIn('menu_id', $menu_ids)
            ->where('level', DBCont::LEVEL_ACTION)
            ->orderByRaw('sort asc, id desc')
            ->get()
            ->map(function ($item) {
                return (array)$item;
            })
            ->toArray();

        $menu_list = [];
        foreach ($list as $item) {
            $menu_list[$item['menu_id']][] = $item;
        }

        return $menu_list;
    }

    /**
     * 生成节点的 url 地址。
     *
     * 迁自 QsController:_node_url1（纯逻辑 + 全局 U()，无 $this 依赖）。
     */
    private function nodeUrl1(array $node): string
    {
        if ($node['url']) {
            return $node['url'];
        } else {
            return U($node['url_name']);
        }
    }
}
