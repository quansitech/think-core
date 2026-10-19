<?php
namespace Behavior;

use Illuminate\Container\Container;
use Qscmf\Contracts\RbacCheckerInterface;
use Qscmf\Core\AuthStarter;
use Qscmf\Core\BackendInitializer;
use Qscmf\Core\RbacChecker;

/**
 * 行为扩展：初始化 Laravel 依赖注入容器单例。
 *
 * 在 web 入口（tp.php → ThinkPHP.php → App::run()）下，全局没有任何位置
 * 调用 Container::setInstance()，导致 app() helper 返回 null，无法使用 DI。
 * 本行为在 app_init 阶段建立容器单例，使 app()->make() 在整个请求生命周期可用。
 *
 * 设计要点：
 * - 幂等：若已有容器单例则保留，避免覆盖 EloquentLoadBehavior 等已建立的容器。
 * - 轻量：使用 Illuminate\Container\Container 而非完整的 Application，避免引入
 *   不必要的引导开销（ThinkPHP 不需要 Laravel 的 Kernel/Router 体系）。
 * - 向后兼容：容器建立后，旧代码的 new $class() 不受影响；只有显式调用
 *   app()->make() 的代码才会走容器解析。
 * - 默认绑定：注册核心接口到实现的映射（如 RBAC 决策器），测试时可覆盖。
 */
class ContainerInitBehavior {

    public function run(&$params = null) {
        $container = Container::getInstance();
        if ($container === null) {
            $container = new Container();
            Container::setInstance($container);
        }

        // 注册核心接口默认绑定。仅当尚未绑定时写入，避免覆盖测试中已设置的 mock。
        if (! $container->bound(RbacCheckerInterface::class)) {
            $container->singleton(RbacCheckerInterface::class, RbacChecker::class);
        }

        // 鉴权协作者：QsController::_initialize 经 qs_instantiate() 解析它做鉴权编排。
        // 测试可 app()->instance(AuthStarter::class, $mock) 整体替换跳过鉴权。
        if (! $container->bound(AuthStarter::class)) {
            $container->singleton(AuthStarter::class);
        }

        // 后台初始化协作者：承载 _initialize 的全部后台初始化逻辑（菜单/Hook/layoutProps）。
        // 测试可 app()->instance(BackendInitializer::class, $mock) 整体替换消除全部副作用。
        if (! $container->bound(BackendInitializer::class)) {
            $container->singleton(BackendInitializer::class);
        }
    }
}

