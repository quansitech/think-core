<?php
return  [
    'SHOW_PAGE_TRACE'       =>  false,

    'USER_AUTH_GATEWAY' => '/Admin/Public/login',

    'RESQUE_JOB_REPEAT_TIMES' => 3,

    //资源调用设置
    'ASSET' => array(
        'prefix' => '/Public/',
    ),

    'LANG_SWITCH_ON'        =>  true,
    'LANG_AUTO_DETECT'      =>  true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'             =>  'zh-cn', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'          =>  'l', // 默认语言切换变量

    'GY_TOKEN_ON'           =>   true,  //公益平台token机制开启
    'TOKEN_ON'              =>   true,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'            =>   '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'            =>   'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'           =>   true,  //令牌验证出错后是否重置令牌 默认为true

    'SHOW_ERROR_MSG'        =>  false,    // 显示错误信息

    /* 模板引擎设置 */
    'TMPL_ACTION_ERROR'     =>  APP_PATH.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  APP_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  APP_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件

    'USER_AUTH_ON'      =>   true, //是否需要认证
    'USER_AUTH_TYPE'    =>   2,  //认证类型
    'USER_AUTH_KEY'     =>   'auth_id', //认证识别号
    'USER_AUTH_MODEL'   =>   'user',
    'USER_AUTH_ADMINID' =>   '1',

    'ADMIN_AUTH_KEY' => 'super_admin',

    'RBAC_ROLE_TABLE' => 'qs_role',
    'RBAC_USER_TABLE' => 'qs_role_user',
    'RBAC_ACCESS_TABLE' => 'qs_access',
    'RBAC_NODE_TABLE' => 'qs_node',

    //分页参数
    'VAR_PAGE' => 'page',

    // 'URL_ROUTER_ON' => true,

    'TMPL_PARSE_STRING' => array(
        '__ADDONSJS__' => __ROOT__ . '/Public/Addons'
    )
];
