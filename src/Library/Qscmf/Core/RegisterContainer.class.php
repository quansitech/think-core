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

    static public function registerController($controller_name, $controller_cls){
        self::$extend_controllers['extends'][$controller_name] = $controller_cls;
    }

    static public function getRegisterController($controller_name){
        return self::$extend_controllers['extends'][$controller_name];
    }
}