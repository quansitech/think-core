<?php
namespace Qscmf\Core;


use Qscmf\Core\Session\DefaultSession;
use Qscmf\Core\Session\ISession;

class AuthChain
{

    const AUTH_RULE_ID = 'AUTH_RULE_ID';
    const AUTH_ROLE_TYPE = 'AUTH_ROLE_TYPE';
    private static $session_obj = null;

    public static function registerSessionCls(ISession $session_obj){
        self::$session_obj = $session_obj;
    }

    // 设置权限过滤标识key的session值
    public static function setAuthFilterKey($role_id, $role_type = ''){
        $role_id = $role_id ? $role_id : null;
        $role_type = ($role_id && $role_type) ? $role_type : null;

        self::$session_obj->set(self::AUTH_RULE_ID, $role_id);
        self::$session_obj->set(self::AUTH_ROLE_TYPE, $role_type);
    }

    // 清空权限过滤标识key的session值
    public static function cleanAuthFilterKey(){
        self::$session_obj->clear(self::AUTH_RULE_ID);
        self::$session_obj->clear(self::AUTH_ROLE_TYPE);
    }

    public static function getAuthRuleId(){
        return self::$session_obj->get(self::AUTH_RULE_ID);
    }

    public static function getAuthRoleType(){
        return self::$session_obj->get(self::AUTH_ROLE_TYPE);
    }

    public static function init(){
        if (is_null(self::$session_obj)){
            self::$session_obj = new DefaultSession();
        }
    }
}