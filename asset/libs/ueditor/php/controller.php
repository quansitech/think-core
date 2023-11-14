<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header

foreach (array(__DIR__ . '/../../../../../../../vendor/autoload.php', __DIR__ .
    '/../../../../../../vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('VENDOR_DIR', dirname($file));

        break;
    }
}
require_once VENDOR_DIR . '/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(VENDOR_DIR . '/..');
$dotenv->load();

date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

if(file_exists(VENDOR_DIR . '/../app/Common/Conf/ueditor_config.json')){
    $config_file = VENDOR_DIR . '/../app/Common/Conf/ueditor_config.json';
}
elseif(file_exists(VENDOR_DIR . '/../app/Common/Conf/ueditor_config.php')){
    $config_file = VENDOR_DIR . '/../app/Common/Conf/ueditor_config.php';
}
else{
    $config_file = "config.json";
}

$http_config = include VENDOR_DIR . '/../app/Common/Conf/Config/http_config.php';
$upload_config = include VENDOR_DIR . '/../app/Common/Conf/Config/upload_config.php';
$common_config = array_merge((array)$http_config, (array)$upload_config);

define('SITE_URL', env('DOMAIN', $_SERVER['HTTP_HOST']). env('ROOT'));

define('HTTP_PROTOCOL', $_SERVER[$common_config['HTTP_PROTOCOL_KEY']]);

function parseUrl($url, $domain = 0, $url_prefix = '', $url_suffix = ''){
    $parsed_url = $url;

    if($url_prefix){
        $parsed_url = rtrim($url_prefix, '/') . $parsed_url;
    }

    if($_GET['url_suffix']){
        $parsed_url = $parsed_url . $url_suffix;
    }

    if($_GET['urldomain']){
        $parsed_url = HTTP_PROTOCOL . '://' . SITE_URL . $url;
    }

    return $parsed_url;
}

$extend = pathinfo($config_file, PATHINFO_EXTENSION);
if ($extend === 'php'){
    $CONFIG = include $config_file;
}else{
    $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($config_file)), true);
}

$action = $_GET['action'];

switch ($action) {
    case 'config':
        $result =  json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':
        $result = include("action_upload.php");
        break;

    /* 获取微信富文本 */
    case 'get_wx_rich_text':
        $result = include("action_wx_rich_text.php");
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        break;

    default:
        $result = json_encode(array(
            'state'=> '请求地址出错'
        ));
        break;
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ));
    }
} else {
    echo $result;
}