<?php
namespace Qscmf\Core;


use Qscmf\Core\AuthChain\CommonAuthChainSession;
use Qscmf\Core\AuthChain\IAuthChainSession;

class AuthChain
{

    const AUTH_RULE_ID = 'AUTH_RULE_ID';
    const AUTH_ROLE_TYPE = 'AUTH_ROLE_TYPE';
    static $session_cls = CommonAuthChainSession::class;

    public static function registerSessionCls($session_cls){
        if (!(new $session_cls()) instanceof IAuthChainSession){
            E('需要实现IAuthChainSession接口');
        }
        self::$session_cls = $session_cls;
    }

    // 设置权限过滤标识key的session值
    public static function setAuthFilterKey($role_id, $role_type = ''){
        $role_id = $role_id ? $role_id : null;
        $role_type = ($role_id && $role_type) ? $role_type : null;

        self::set(self::AUTH_RULE_ID, $role_id);
        self::set(self::AUTH_ROLE_TYPE, $role_type);
    }

    // 清空权限过滤标识key的session值
    public static function cleanAuthFilterKey(){
        self::clear(self::AUTH_RULE_ID);
        self::clear(self::AUTH_ROLE_TYPE);
    }

    public static function get($key){
        return (new self::$session_cls)->get($key);
    }

    public static function set($key, $value){
        (new self::$session_cls)->set($key, $value);
    }

    public static function clear($key){
        (new self::$session_cls)->clear($key);
    }
}