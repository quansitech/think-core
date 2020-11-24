<?php

namespace Qscmf\Lib;

class DBCont{
    const LEVEL_MODULE = 1;
    const LEVEL_CONTROLLER = 2;
    const LEVEL_ACTION = 3;
    
    const FORBIDDEN_STATUS = 0;
    const NORMAL_STATUS = 1;

    const JOB_STATUS_WAITING = 1;
    const JOB_STATUS_RUNNING = 2;
    const JOB_STATUS_FAILED = 3;
    const JOB_STATUS_COMPLETE = 4;

    const YES_BOOL_STATUS = 1;
    const NO_BOOL_STATUS = 0;

    const CLOSE_TYPE_ALL = 1;
    const CLOSE_TYPE_CONNECTION = 2;

    static private $_close_type = array(
        self::CLOSE_TYPE_ALL => 'all',
        self::CLOSE_TYPE_CONNECTION => 'connection'
    );

    static private $_status = array(
        self::NORMAL_STATUS => '正常',
        self::FORBIDDEN_STATUS => '禁用'
    );

    static private $_job_status = array(
        self::JOB_STATUS_WAITING => '等待',
        self::JOB_STATUS_RUNNING => '运行中',
        self::JOB_STATUS_FAILED => '失败',
        self::JOB_STATUS_COMPLETE => '完成'
    );

    static private $_bool_status = array(
        self::YES_BOOL_STATUS => '是',
        self::NO_BOOL_STATUS => '否'
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

