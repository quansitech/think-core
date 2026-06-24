<?php

namespace Qscmf\Core;

use AntdAdmin\Controller\HasLayoutProps;
use App\Models\Menu;
use App\Models\Node;
use Behavior\HeadCssBehavior;
use Behavior\HeadJsBehavior;
use Behavior\InjectHeadBehavior;
use Gy_Library\DBCont;
use Illuminate\Database\Capsule\Manager as Capsule;
use Qscmf\Contracts\RbacCheckerInterface;
use Think\Controller;
use Think\Hook;

class QsController extends Controller {

    use HasLayoutProps;

    /**
     * 鉴权协作者：经构造注入（容器自动解析），承载 resetRbac/verifyLogin/authorize 三步。
     * 测试可直接 new 时传入 mock，或 app()->instance(AuthStarter::class, $mock) 经容器替换。
     *
     * @var AuthStarter
     */
    protected AuthStarter $authStarter;

    /**
     * RBAC 权限决策器。通过容器解析（可 mock）；解析失败时回退到默认实现，
     * 保证生产环境行为不变。
     *
     * @var RbacCheckerInterface|null
     */
    protected $rbac = null;

    /**
     * 构造函数：经容器自动注入 AuthStarter（构造注入，非服务定位）。
     *
     * 关键顺序：必须先存 $this->authStarter，再调 parent::__construct() ——
     * 因为 Think\Controller 基类构造内部会调 _initialize()，而 _initialize 依赖 $this->authStarter。
     *
     * 控制器实例化经 controller() → qs_instantiate() → 容器 make()，
     * 容器会沿继承链解析 QsController 构造参数（子类无自定义构造则自动继承）。
     * 故 19 个业务控制器（无自定义 __construct）无需任何改动即可获得注入。
     *
     * @param AuthStarter|null $authStarter 容器自动注入；为兼容非容器实例化（如旧式 new）
     *                                       允许 null，此时由 getAuthStarter() 兜底解析。
     */
    public function __construct(?AuthStarter $authStarter = null)
    {
        // 容器未注入时兜底（兼容直接 new QsController() 的边界场景）
        $this->authStarter = $authStarter ?? $this->resolveAuthStarter();
        parent::__construct();
    }

    /**
     * 从容器解析 AuthStarter 兜底（容器不可用时 new）。
     * 仅在构造参数未注入时调用，正常路径走构造注入。
     */
    private function resolveAuthStarter(): AuthStarter
    {
        $container = qs_container();
        if ($container !== null) {
            try {
                return $container->make(AuthStarter::class);
            } catch (\Throwable $e) {
                return new AuthStarter();
            }
        }
        return new AuthStarter();
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

    protected function _initialize(){


        // 鉴权协作者（多用户表配置切换）：所有 QsController 子类均执行，保持原 _resetRbac 语义。
        // $this->authStarter 经构造注入（容器自动解析），测试可直接传入 mock。
        $this->authStarter->resetRbac();

        //未使用ajax前，暂时使用
        //将后台菜单存入缓存
        if(in_array(strtolower(MODULE_NAME), (array)C("BACKEND_MODULE"))){

            //开启预加载js钩子
            Hook::import(['view_filter' => [HeadCssBehavior::class]], true);
            Hook::import(['view_filter' => [HeadJsBehavior::class]], true);
            Hook::import(['view_filter' => [\Behavior\BodyHtmlBehavior::class]], true);
            Hook::import(['view_filter' => [\Behavior\HeaderNavbarRightHtmlBehavior::class]], true);

            // 解析模板时注入需要引入扩展包css/js标识
            Hook::add('parse_extend', InjectHeadBehavior::class);

            // 解析模板时在body标签底部注入html
            Hook::add('parse_extend', \Behavior\InjectBodyBehavior::class);

            // 验证登录用户的状态（协作者触发 verify_login_user 标签）
            $this->authStarter->verifyLogin();

            $menu = qs_instantiate(Menu::class);

            //顶部菜单栏
            $top_menu_list = $menu->getMenuList('top_menu');
            $this->assign('top_menu', $top_menu_list);
            $this->assign('current_module', strtolower(MODULE_NAME));

            $top_menu_id = 0;
            foreach($top_menu_list as $top_menu){
                if($top_menu['module'] == strtolower(MODULE_NAME)){
                    $top_menu_id = $top_menu['id'];
                    break;
                }
            }

            $menu_list = $menu->getMenuList('backend_menu', $top_menu_id);
            //要在左边栏显示的菜单
            $show_list  = array();


            $menu_ids = array_column((array)$menu_list, "id");
            !empty($menu_ids) && $node_group_with_menu = $this->_nodeGroupWithMenu($menu_ids);

            for ($i = 0, $iMax = count((array)$menu_list); $i< $iMax; $i++){
                $node_list = $node_group_with_menu[$menu_list[$i]['id']];

                $show_node_list = array();
                $add_flag = false;
                for($n = 0, $nMax = count((array)$node_list); $n< $nMax; $n++){
                    $node = $node_list[$n];
                    $node_id = $node['id'];
                    if($this->resolveRbac()->checkAccessNodeId(session(C('USER_AUTH_KEY')), $node_id)){
                        $node_list[$n]['url'] = $this->_node_url1($node);
                        $show_node_list[] = $node_list[$n];
                        $add_flag = true;
                    }
                }
                //只显示有权限操作的菜单项
                if($add_flag){

                    $menu_list[$i]['node_list'] = $show_node_list;
                    $show_list[] = $menu_list[$i];
                }
            }
            $backend_menu = $show_list;
            $this->assign('menu_list', $backend_menu);
        }

        // RBAC 访问决策（协作者；不过权限则 E(l('no_auth')) 中止）
        $this->authStarter->authorize();

        if (C('ANTD_ADMIN_BUILDER_ENABLE')) {
            $this->handleLayoutProps();
        }

        $this->flashError();
        $this->flashInput();
    }

    private function flashInput(){
        if(IS_POST){
            $post_data = I('post.');
            foreach($post_data as $k => $v){
                Flash::set('qs_old_input.' . $k, $v);
            }
        }
    }

    private function flashError(){
        $this->errors = FlashError::all();
    }

    private function _nodeGroupWithMenu($menu_ids):array{
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
        foreach($list as $item){
            $menu_list[$item['menu_id']][] = $item;
        }

        return $menu_list;
    }

    //生成节点的url地址
    private function _node_url($node_id){
        $action = Node::find($node_id);
        if(!$action){
            return '';
        }
        $action = $action->toArray();

        if($action['url']){
            return $action['url'];
        }
        else{
            $controller = Node::find($action['pid']);
            if(!$controller){
                return '';
            }
            $controller = $controller->toArray();

            $module = Node::find($controller['pid']);
            if(!$module){
                return '';
            }
            $module = $module->toArray();

            $url = U($module['name'] . '/' . $controller['name'] . '/' . $action['name']);
            return $url;
        }
    }

    private function _node_url1($node){
        if($node['url']){
            return $node['url'];
        }
        else{
            return U($node['url_name']);
        }
    }

    protected function success($message = '', $jumpUrl = '', $ajax = false) {
        //$refer_url = I('get.refer_url');
//        show_bug($refer_url);
//        exit();
        //$jumpUrl = empty($jumpUrl) && !empty($refer_url) ? urldecode($refer_url) : $jumpUrl;

        parent::success($message, $jumpUrl, $ajax);
    }

}
