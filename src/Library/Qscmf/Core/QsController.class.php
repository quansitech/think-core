<?php

namespace Qscmf\Core;

use AntdAdmin\Controller\HasLayoutProps;
use Qscmf\Contracts\RbacCheckerInterface;
use Think\Controller;

class QsController extends Controller {

    // 复用 quansitech/antd-admin 的 HasLayoutProps trait：
    //   - setActiveNid()/isInertia() 供业务控制器 action 内部调用（protected 可见）。
    //   - handleLayoutProps 由 BackendInitializer 经下面的 public handleLayoutProps()
    //     入口调用（外部调 public 可见），内部委托给 doHandleLayoutProps()（trait 的
    //     protected 原方法，控制器内部调用可见），避免 protected 外部调用触发 __call。
    use HasLayoutProps {
        HasLayoutProps::handleLayoutProps as protected doHandleLayoutProps;
    }

    /**
     * Inertia layoutProps 注入入口（public），供 BackendInitializer 跨对象调用。
     * 内部委托给 trait 的 protected doHandleLayoutProps()。
     */
    public function handleLayoutProps()
    {
        $this->doHandleLayoutProps();
    }

    /**
     * 重定向到认证网关（public），供 BackendInitializer 跨对象调用。
     * 复刻 v14 QsController::_initialize 里未登录时的 $this->redirect(C('USER_AUTH_GATEWAY'))。
     * 因 Think\Controller::redirect 为 protected，外部协作者无法直接调用，故暴露此入口。
     */
    public function redirectToGateway(string $gateway): void
    {
        $this->redirect($gateway);
    }

    /**
     * 鉴权协作者：经构造注入（容器自动解析），承载 resetRbac/verifyLogin/authorize 三步。
     * 测试可直接 new 时传入 mock，或 app()->instance(AuthStarter::class, $mock) 经容器替换。
     *
     * @var AuthStarter
     */
    protected AuthStarter $authStarter;

    /**
     * 后台初始化协作者：承载 _initialize 的全部后台初始化逻辑（菜单/Hook/layoutProps）。
     * 测试可 app()->instance(BackendInitializer::class, $mock) 整体替换消除全部副作用。
     *
     * @var BackendInitializer
     */
    protected BackendInitializer $backendInitializer;

    /**
     * RBAC 决策器（懒加载缓存）：resolveRbac() 首次解析后存此，避免重复走容器。
     * 用 nullable + 声明类型，防止 PHP 8.3 把 $this->rbac 动态属性判定为 deprecated。
     *
     * @var RbacCheckerInterface|null
     */
    protected ?RbacCheckerInterface $rbac = null;

    /**
     * 构造函数：经容器自动注入 AuthStarter + BackendInitializer（构造注入）。
     *
     * 关键顺序：必须先存 $this->authStarter / $this->backendInitializer，再调
     * parent::__construct() —— 因为 Think\Controller 基类构造内部会调 _initialize()，
     * 而 _initialize 依赖 $this->backendInitializer。
     *
     * 控制器实例化经 controller() → qs_instantiate() → 容器 make()，
     * 容器会沿继承链解析 QsController 构造参数（子类无自定义构造则自动继承）。
     * 故 19 个业务控制器（无自定义 __construct）无需任何改动即可获得注入。
     *
     * @param AuthStarter|null $authStarter 容器自动注入；为兼容非容器实例化允许 null
     * @param BackendInitializer|null $backendInitializer 同上
     */
    public function __construct(?AuthStarter $authStarter = null, ?BackendInitializer $backendInitializer = null)
    {
        // 容器未注入时兜底（兼容直接 new QsController() 的边界场景）
        $this->authStarter = $authStarter ?? $this->resolveFromContainer(AuthStarter::class, fn() => new AuthStarter());
        $this->backendInitializer = $backendInitializer ?? $this->resolveFromContainer(
            BackendInitializer::class,
            fn() => new BackendInitializer($this->resolveRbac(), $this->authStarter)
        );
        parent::__construct();
    }

    /**
     * 从容器解析协作者兜底（容器不可用时用回退工厂）。
     */
    private function resolveFromContainer(string $class, \Closure $fallback)
    {
        $container = qs_container();
        if ($container !== null) {
            try {
                return $container->make($class);
            } catch (\Throwable $e) {
                return $fallback();
            }
        }
        return $fallback();
    }

    /**
     * 获取 RBAC 决策器（懒加载）。
     *
     * 优先从 DI 容器解析，使测试可通过 app()->instance(RbacCheckerInterface::class, $mock)
     * 替换；容器不可用时回退到默认实现 RbacChecker（包装原有 QsRbac 静态调用）。
     *
     * @return RbacCheckerInterface
     */
    protected function resolveRbac(): RbacCheckerInterface
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

    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix=''){

        if(C('GY_TOKEN_ON') && !C('TOKEN_ON')){
            C('TOKEN_ON', true);
        }

        parent::display($templateFile, $charset, $contentType, $content, $prefix);
    }

    //检验是否重复提交表单，重复提交则原页面重定向
    protected function autoCheckToken($url = ''){

        if(!empty($_POST)){
            // ajax无刷新提交，不检验token
            if(!IS_AJAX){
                $result = $this->checkToken($_POST);
                if(!$result){
                    if($url == ''){
                        redirect(U('/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME));
                    }
                    else{
                        redirect($url);
                    }
                }
                if(C('GY_TOKEN_ON') && C('TOKEN_ON')){
                    C('TOKEN_ON', false);
                }
            }
        }
        C('TOKEN_ON', false);
        return true;
    }

    private function checkToken($data){
        if(C('TOKEN_ON')){
            $name = C('TOKEN_NAME', null, '__hash__');
            if(!isset($data[$name]) || !isset($_SESSION[$name])){
                return false;
            }
            list($key, $value) = explode('_', $data[$name]);
            if($value && $_SESSION[$name][$key] === $value){
                unset($_SESSION[$name][$key]);
                return true;
            }
            if(C('TOKEN_RESET')){
                unset($_SESSION[$name][$key]);
            }
            return false;
        }
        return true;
    }

    /**
     * 控制器初始化：全部后台初始化逻辑（鉴权/菜单/Hook/layoutProps）委托给
     * BackendInitializer 协作者。测试经容器绑定 mock 整个协作者即可消除全部副作用。
     *
     * @param QsController $this 传入控制器引用，供协作者触发 trait 方法（handleLayoutProps）
     *                          和写视图变量（经 __set → assign）
     */
    protected function _initialize(){
        $this->backendInitializer->initialize($this);
    }

    protected function success($message = '', $jumpUrl = '', $ajax = false) {
        //$refer_url = I('get.refer_url');
//        show_bug($refer_url);
//        exit();
        //$jumpUrl = empty($jumpUrl) && !empty($refer_url) ? urldecode($refer_url) : $jumpUrl;

        parent::success($message, $jumpUrl, $ajax);
    }

}
