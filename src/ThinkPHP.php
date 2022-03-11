<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------




//----------------------------------
// ThinkPHP公共入口文件
//----------------------------------
// 记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);
// 记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();

// 版本信息
const THINK_VERSION     =   '3.2.3';

// URL 模式定义
const URL_COMMON        =   0;  //普通模式
const URL_PATHINFO      =   1;  //PATHINFO模式
const URL_REWRITE       =   2;  //REWRITE模式
const URL_COMPAT        =   3;  // 兼容模式

// 类文件后缀
const EXT               =   '.class.php';

require __DIR__ . '/ConstDefine.php';

// 系统信息
if(version_compare(PHP_VERSION,'5.4.0','<')) {
    ini_set('magic_quotes_runtime',0);
    define('MAGIC_QUOTES_GPC',false);
}else{
    define('MAGIC_QUOTES_GPC',false);
}
defined("IS_CGI") || define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
defined("IS_WIN") || define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
defined("IS_CLI") || define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(env("APP_MAINTENANCE", false) && (!isset($_SERVER['argv'])  ||  !isset($_SERVER['argv'][2]) || $_SERVER['argv'][2] != 'maintenance'))
{
    if(!IS_CLI){
        header('HTTP/1.1 503 Service Unavailable');
        // 确保FastCGI模式下正常
        header('Status:503 Service Unavailable');
    }

    echo '系统维护中，请稍后再尝试';
    exit();
}

if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
    }
}

// 加载核心Think类
require CORE_PATH.'Think'.EXT;
// 应用初始化 
Think\Think::start();