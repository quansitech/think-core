<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
set_time_limit(0);
include("Uploader.class.php");

/* 上传配置 */
$config = array(
    "pathFormat" => $CONFIG['catcherPathFormat'],
    "maxSize" => $CONFIG['catcherMaxSize'],
    "allowFiles" => $CONFIG['catcherAllowFiles'],
    "oriName" => "remote.png"
);
$fieldName = $CONFIG['catcherFieldName'];

/* 抓取远程图片 */
$list = array();
if (isset($_POST[$fieldName])) {
    $source = $_POST[$fieldName];
} else {
    $source = $_GET[$fieldName];
}

$oss = $_GET['oss'];
if($oss){
  $type = $_GET['type'];
  if(!$type){
    $type = 'image';
  }
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

  foreach ($source as $imgUrl) {
      $item = new Uploader($imgUrl, $config, "remote");
      $info = $item->getFileInfo();
      $file = realpath(VENDOR_DIR . '/../www' . $info['url']);
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

      array_push($list, array(
          "state" => $info["state"],
          "url" => $info["url"],
          "size" => $info["size"],
          "title" => htmlspecialchars($info["title"]),
          "original" => htmlspecialchars($info["original"]),
          "source" => $imgUrl
      ));
  }

  /* 返回抓取数据 */
  return json_encode(array(
      'state'=> count($list) ? 'SUCCESS':'ERROR',
      'list'=> $list
  ));
}
else{
    foreach ($source as $imgUrl) {
      $item = new Uploader($imgUrl, $config, "remote");
      $info = $item->getFileInfo();
      $info['url'] = parseUrl($info['url'], $_GET['urldomain'], $_GET['url_prefix'], $_GET['url_suffix']);
      array_push($list, array(
          "state" => $info["state"],
          "url" => $info["url"],
          "size" => $info["size"],
          "title" => htmlspecialchars($info["title"]),
          "original" => htmlspecialchars($info["original"]),
          "source" => $imgUrl
      ));
    }

    /* 返回抓取数据 */
    return json_encode(array(
      'state'=> count($list) ? 'SUCCESS':'ERROR',
      'list'=> $list
    ));
}
