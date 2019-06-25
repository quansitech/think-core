<?php

namespace Qscmf\Lib;

class DBCont{
    const LEVEL_MODULE = 1;
    const LEVEL_CONTROLLER = 2;
    const LEVEL_ACTION = 3;
    
    const FORBIDDEN_STATUS = 0;
    const NORMAL_STATUS = 1;

    static private $_status = array(
        self::NORMAL_STATUS => '正常',
        self::FORBIDDEN_STATUS => '禁用'
    );

    static function __callStatic($name, $arguments)
    {
        $getListFn = function($var_name){
            return self::$$var_name;
        };

        $getListValueFn = function($var_name) use ($arguments){

            return (self::$$var_name)[$arguments[0]];
        };

        $static_name = '_';
        if(preg_match("/get(\w+)List/", $name, $matches)){
            $static_name .= parse_name($matches[1]);
            $fn = $getListFn;
        }
        elseif(preg_match("/get(\w+)/", $name, $matches)){
            $static_name .= parse_name($matches[1]);
            $fn = $getListValueFn;
        }

        return $fn($static_name);
    }
    
}

