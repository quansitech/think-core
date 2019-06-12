<?php

return [
    'USER_AUTH_ON'      =>   true, //是否需要认证
    'USER_AUTH_TYPE'    =>   2,  //认证类型
    'USER_AUTH_KEY'     =>   'auth_id', //认证识别号
    'USER_AUTH_MODEL'   =>   'user',
    'USER_AUTH_ADMINID' =>   '1',

    'RBAC_ROLE_TABLE' => 'qs_role',
    'RBAC_USER_TABLE' => 'qs_role_user',
    'RBAC_ACCESS_TABLE' => 'qs_access',
    'RBAC_NODE_TABLE' => 'qs_node',

    'ADMIN_AUTH_KEY' => 'super_admin'
];