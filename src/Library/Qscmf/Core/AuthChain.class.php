<?php
namespace Qscmf\Core;


class AuthChain
{
    // 设置权限过滤标识key的session值
    public function setAuthFilterKey($role_id, $role_type = ''){
        $role_id = $role_id ? $role_id : null;
        $role_type = ($role_id && $role_type) ? $role_type : null;

        session('AUTH_RULE_ID', $role_id);
        session('AUTH_ROLE_TYPE', $role_type);
    }

    // 清空权限过滤标识key的session值
    public function cleanAuthFilterKey(){
        session('AUTH_RULE_ID', null);
        session('AUTH_ROLE_TYPE', null);
    }

}