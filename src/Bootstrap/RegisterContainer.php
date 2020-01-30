<?php
namespace Bootstrap;

class RegisterContainer{
    static protected $form_item = [];
    static protected $extend_controllers = [];
    static protected $sym_links = [];
    static protected $list_topbutton = [];

    /**
     * @param $link_path 软连接文件地址
     * @param $source_path 源文件地址
     */
    static public function registerSymLink($link_path, $source_path){
        if(isset(self::$sym_links[$link_path])){
            E('存在冲突软连接');
        }

        self::$sym_links[$link_path] = $source_path;
    }

    static public function getRegisterSymLinks(){
        return self::$sym_links;
    }

    static public function registerListTopButton($type, $type_cls){
        self::$list_topbutton[$type] = $type_cls;
    }

    static public function getListTopButtons(){
        return self::$list_topbutton;
    }

    static public function registerFormItem($type, $type_cls){
        self::$form_item[$type] = $type_cls;
    }

    static public function getFormItems(){
        return self::$form_item;
    }

    static public function registerController($module_name, $controller_name, $controller_cls){
        if(self::existRegisterController($module_name, $controller_name)){
            E('注册控制器存在冲突');
        }

        if(class_exists($controller_cls)){
            self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)] = $controller_cls;
        }
        else{
            E('需要注册的类不存在');
        }
    }

    static public function getRegisterController($module_name, $controller_name){
        return self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)];
    }

    static public function existRegisterController($module_name, $controller_name){
        return isset(self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)]);
    }

    static public function existRegisterModule($module_name){
        return isset(self::$extend_controllers[strtolower($module_name)]);
    }
}