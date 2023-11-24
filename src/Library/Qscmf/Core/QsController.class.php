<?php

namespace Qscmf\Core;

use Behavior\HeadCssBehavior;
use Behavior\HeadJsBehavior;
use Behavior\InjectHeadBehavior;
use Behavior\PreloadJsBehavior;
use Think\Controller;
use Gy_Library\DBCont;
use Think\Hook;

class QsController extends Controller {

    public function __construct()
    {
        parent::__construct();
    }

    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix=''){

        if(C('GY_TOKEN_ON') && !C('TOKEN_ON')){
            C('TOKEN_ON', true);
        }

        parent::display($templateFile, $charset, $contentType, $content, $prefix);
    }

    //检验是否重复提交表单，重复提交则原页面重定向
    protected  function autoCheckToken($url = ''){

        if(!empty($_POST)){
            $model = new QsModel();

            if(!$model->autoCheckToken($_POST)){
                if($url == ''){
                    redirect(U('/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME));
                }
                else{
                    redirect($url);
                }
            }
        }
        C('TOKEN_ON', false);
        return true;
    }

    protected function _initialize(){

        //初始化查询条件，所有

        $this->dbname = $this->dbname ? $this->dbname : 'Common/' . CONTROLLER_NAME;

        $this->_resetRbac();

        //未使用ajax前，暂时使用
        //将后台菜单存入缓存
        if(in_array(strtolower(MODULE_NAME), (array)C("BACKEND_MODULE"))){

            if(!isAdminLogin()){
                $this->redirect(C('USER_AUTH_GATEWAY'));
            }

            //开启预加载js钩子
            Hook::import(['view_filter' => [HeadCssBehavior::class]], true);
            Hook::import(['view_filter' => [HeadJsBehavior::class]], true);
            Hook::import(['view_filter' => [\Behavior\BodyHtmlBehavior::class]], true);
            Hook::import(['view_filter' => [\Behavior\HeaderNavbarRightHtmlBehavior::class]], true);

            // 解析模板时注入需要引入扩展包css/js标识
            Hook::add('parse_extend', InjectHeadBehavior::class);

            // 解析模板时在body标签底部注入html
            Hook::add('parse_extend', \Behavior\InjectBodyBehavior::class);

            //非正常状态用户禁止登录后台
            $user_ent = D(C('USER_AUTH_MODEL'))->find(session(C('USER_AUTH_KEY')));
            if($user_ent['status'] != DBCont::NORMAL_STATUS){
                E('用户状态异常');
            }

            $menu = D("Menu");

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
                    if(QsRbac::checkAccessNodeId(session(C('USER_AUTH_KEY')), $node_id)){
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

        if(!QsRbac::AccessDecision()){
            E(l('no_auth'));
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
        $node_group_with_menu_map['status'] = DBCont::NORMAL_STATUS;
        $node_group_with_menu_map['menu_id'] = ['IN', $menu_ids];
        $node_group_with_menu_map['level'] = DBCont::LEVEL_ACTION;
        return $this->_fetchNodeListGroupByMenu($node_group_with_menu_map);
    }

    private function _fetchNodeListGroupByMenu($map):array{
        $list = D()->table(buildNodeVSql().' n_v')->where($map)->order("sort asc")->select();
        $menu_list = [];
        collect($list)->each(function ($item) use(&$menu_list){
            $menu_list[$item['menu_id']][] = $item;
        });

        return $menu_list;
    }

    //生成节点的url地址
    private function _node_url($node_id){
        $node = D("Node");
        $action = $node->find($node_id);
        if($action['url']){
            return $action['url'];
        }
        else{
            $controller = $node->find($action['pid']);
            $module = $node->find($controller['pid']);
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

    // 根据用户配置重置RBAC用户表和用户与用户组关联表
    private function _resetRbac(){
        $inject_rbac_arr = C('INJECT_RBAC');
        if (!empty($inject_rbac_arr)){
            array_map(function ($str){
                if (session("?{$str['key']}")){
                    C('USER_AUTH_MODEL', $str['user'], 'User');
                    C('RBAC_USER_TABLE', $str['role_user'], 'qs_role_user');
                }
            }, $inject_rbac_arr);
        }
    }


}
