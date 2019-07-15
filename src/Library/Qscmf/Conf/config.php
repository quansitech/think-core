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

    'ADMIN_AUTH_KEY' => 'super_admin',

    'URL_ROUTER_ON'         =>  true,   // 是否开启URL路由

    //qiniu up config sample
//    'UPLOAD_TYPE_AUDIO' => array(
//        'mimes'    => 'video/mp4,audio/mp3,audio/x-m4a,audio/mpeg', //允许上传的文件MiMe类型
//        'maxSize'  => 300*1024*1024, //上传的文件大小限制 (0-不做限制)
//        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，>多个参数使用数组
//        'pfopOps' => "avthumb/mp3/ab/160k/ar/44100/acodec/libmp3lame",
//        'pipeline' => 'gdufs_audio',
//        'bucket' => 'gdufs',
//        'domain' => 'https://media.t4tstudio.com'
//    ),

];