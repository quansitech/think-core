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
    $common_http_config = include VENDOR_DIR . '/../app/Common/Conf/Config/http_config.php';
    $common_upload_config = include VENDOR_DIR . '/../app/Common/Conf/Config/upload_config.php';
    $common_config = array_merge((array)$common_http_config, (array)$common_upload_config);

    if (class_exists('\FormItem\ObjectStorage\Lib\Vendor\Context')) {
        return existsOsPackage($type, $common_config, $file_urls, $upload_config, $upload_type);
    } else {
        return toOss($type, $common_config, $file_urls, $upload_config, $upload_type);
    }
}

function combineOsFileUrl($vendor_cls, $objet):string{
    $host_key = $vendor_cls->getVendorConfig()->getHostKey();
    $host = $vendor_cls->getUploadConfig()->getAll()[$host_key];
    // oss_public_host
    return $host.'/'.$objet;
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
    if ($oss_type['oss_options'] && $oss_type['oss_options']['bucket']) {
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
    $header_options = array(\OSS\OssClient::OSS_HEADERS => $oss_type['oss_meta']);
    $oss_client->setConnectTimeout(30);

    $new_info_list = [];

    foreach ($file_urls as $imgUrl) {
        $item = new Uploader($imgUrl, $upload_config, $upload_type);
        $info = $item->getFileInfo();
        if($info['state'] != 'SUCCESS'){
            $new_info_list[] = $info;
            continue;
        }
        $file = realpath(VENDOR_DIR . '/../www' . $info['url']);
        replaceHeader($header_options, $info);
        $r = $oss_client->uploadFile($oss_config['bucket'], trim($info['url'], '/'), $file, $header_options);
        unlink($file);

        if(isset($oss_type['oss_public_host'])){
            $public_url = parse_url($oss_type['oss_public_host']);
            $internal_url = parse_url($oss_type['oss_host']);
            $oss_request_url = str_replace($internal_url['host'], $public_url['host'], $r['oss-request-url']);
        }
        else{
            $oss_request_url = $r['oss-request-url'];
        }
        $info['url'] = parseUrl($oss_request_url , 0, $_GET['url_prefix'], $_GET['url_suffix']);

        $new_info_list[] = array(
            "state" => $info["state"],
            "url" => $info["url"],
            "size" => $info["size"],
            "title" => htmlspecialchars($info["title"]),
            "original" => htmlspecialchars($info["original"]),
            "source" => $imgUrl
        );
    }

    return $new_info_list;
}

function replaceHeader(&$header_options, $info){
    if($header_options[\OSS\OssClient::OSS_HEADERS]['Content-Disposition']){
        $header_options[\OSS\OssClient::OSS_HEADERS]['Content-Disposition'] = str_replace('__title__', $info['original'], $header_options[\OSS\OssClient::OSS_HEADERS]['Content-Disposition']);
    }
}

