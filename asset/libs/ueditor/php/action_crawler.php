<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
set_time_limit(0);
include("Uploader.class.php");
include("os_uploader.php");

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
if($oss || $_GET['os']){
  $type = $_GET['type'];
  if(!$type){
    $type = 'image';
  }
  $upload_res_list = osUpload($type,$source, $config, "remote");
  $list = array_merge($list, $upload_res_list);

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
