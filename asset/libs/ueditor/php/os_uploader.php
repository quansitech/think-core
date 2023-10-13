<?php

foreach (array(__DIR__ . '/../../../../../../../vendor/autoload.php', __DIR__ .
    '/../../../../../../vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('VENDOR_DIR', dirname($file));

        break;
    }
}
require_once VENDOR_DIR . '/autoload.php';

$dotenv = \Dotenv\Dotenv::create(VENDOR_DIR . '/..');
$dotenv->load();

function osUpload($type, $file_urls, $upload_config, $upload_type){
    $common_config = include VENDOR_DIR . "/../app/Common/Conf/config.php";

    if (class_exists('\FormItem\ObjectStorage\Lib\Vendor\Context')) {
        return existsOsPackage($type, $common_config, $file_urls, $upload_config, $upload_type);
    } else {
        return toOss($type, $common_config, $file_urls, $upload_config, $upload_type);
    }
}

function combineOsFileUrl($vendor_cls, $objet):string{
    $host_key = $vendor_cls->getVendorConfig()->getHostKey();
    return $vendor_cls->getUploadConfig()->getAll()[$host_key].'/'.$objet;
}

function getHeaderOptions($vendor_cls):array{
    return $vendor_cls->getUploadConfig()->getMeta();
}

function existsOsPackage($type, $common_config, $file_urls, $upload_config, $upload_type){
    $upload_type_config = $common_config['UPLOAD_TYPE_' . strtoupper($type)];
    $vendor_type = $_GET['vendor_type'];

    if ($_GET['oss']){
        $vendor_type = \FormItem\ObjectStorage\Lib\Vendor\Context::VENDOR_ALIYUN_OSS;
    }else{
        $vendor_type = \FormItem\ObjectStorage\Lib\Common::getVendorType($type,$vendor_type,$upload_type_config);
    }

    $os_client = \FormItem\ObjectStorage\Lib\Vendor\Context::genVendorByType($vendor_type);
    $os_client->setUploadConfig($type, $upload_type_config);

    $new_info_list = [];

    foreach ($file_urls as $one_file) {
        $item = new Uploader($one_file, $upload_config, $upload_type);
        $info = $item->getFileInfo();
        if($info['state'] != 'SUCCESS'){
            $new_info_list[] = $info;
            continue;
        }
        $file = realpath(VENDOR_DIR . '/../www' . $info['url']);
        $r = $os_client->genClient($type, false)->uploadFile($file, trim($info['url'], '/'), getHeaderOptions($os_client));
        unlink($file);
        $info['url'] = parseUrl(combineOsFileUrl($os_client, $r) , 0, $_GET['url_prefix'], $_GET['url_suffix']);

        $new_info_list[] = [
            "state" => $info["state"],
            "url" => $info["url"],
            "size" => $info["size"],
            "title" => htmlspecialchars($info["title"]),
            "original" => htmlspecialchars($info["original"]),
            "source" => $one_file
        ];
    }

    return $new_info_list;
}

function toOss($type, $common_config, $file_urls, $upload_config, $upload_type){
    $oss_type = $common_config['UPLOAD_TYPE_' . strtoupper($type)];

    $is_cname=false;
    if ($oss_type['oss_options']) {
        $bucket=$oss_type['oss_options']['bucket'];
        $endpoint = $oss_type['oss_host'];
        $is_cname=true;
    }else{
        $url = $oss_type['oss_host'];
        $rt = parse_url($url);
        $arr = explode('.', $rt['host']);
        $bucket = array_shift($arr);
        $endpoint = $rt['scheme'] . '://' . join('.', $arr);
    }

    $oss_config = array(
        "ALIOSS_ACCESS_KEY_ID" => $common_config['ALIOSS_ACCESS_KEY_ID'],
        "ALIOSS_ACCESS_KEY_SECRET" => $common_config["ALIOSS_ACCESS_KEY_SECRET"],
        "end_point" => $endpoint,
        "bucket" => $bucket
    );

    spl_autoload_register(function($class){
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = VENDOR_DIR  . "/../app/Common/Util" . DIRECTORY_SEPARATOR . $path . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });

    $oss_client = new \OSS\OssClient($oss_config['ALIOSS_ACCESS_KEY_ID'], $oss_config['ALIOSS_ACCESS_KEY_SECRET'], $oss_config['end_point'],$is_cname);
    $oss_client->setConnectTimeout(30);
    $header_options = array(\OSS\OssClient::OSS_HEADERS => $oss_type['oss_meta']);

    $new_info_list = [];

    foreach ($file_urls as $one_file) {
        $item = new Uploader($one_file, $upload_config, $upload_type);
        $info = $item->getFileInfo();
        if($info['state'] != 'SUCCESS'){
            $new_info_list[] = $info;
            continue;
        }
        $file = realpath(VENDOR_DIR . '/../www' . $info['url']);
        $r = $oss_client->uploadFile($oss_config['bucket'], trim($info['url'], '/'), $file, $header_options);
        unlink($file);
        $info['url'] = parseUrl($r['oss-request-url'] , 0, $_GET['url_prefix'], $_GET['url_suffix']);

        $new_info_list[] = [
            "state" => $info["state"],
            "url" => $info["url"],
            "size" => $info["size"],
            "title" => htmlspecialchars($info["title"]),
            "original" => htmlspecialchars($info["original"]),
            "source" => $one_file
        ];
    }

    return $new_info_list;
}

