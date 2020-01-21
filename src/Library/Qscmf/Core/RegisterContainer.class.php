<?php
namespace Qscmf\Core;

use Qscmf\Builder\FormType\FormType;

class RegisterContainer{
    static protected $form_item = [];
    static protected $extend_controllers = [];

    static public function registerFormItem($type, $type_cls){
        self::$form_item[$type] = $type_cls;
    }

    static public function getFormItems(){
        return self::$form_item;
    }

    static public function registerController($module_name, $controller_name, $controller_cls){
        if(class_exists($controller_cls)){
            self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)] = $controller_cls;
        }
        else{
            E('主要注册的类不存在');
        }
    }

    static public function getRegisterController($module_name, $controller_name){
        return self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)];
    }

    static public function existRegisterController($module_name, $controller_name){
        return isset(self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)]);
    }
}