<?php
namespace Qscmf\Builder\ColumnType\Picture;

use Qscmf\Builder\ColumnType\ColumnType;
use Think\View;

class Picture extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        // 如果是数组，则使用viewerjs（默认情况下不使用）
        if (is_array($data[$option['name']])){
            $image['original'] = showFileUrl($data[$option['name']]['original']); // 原图
            $image['src'] = showFileUrl($data[$option['name']]['src']); // 缩略图
        }else{
            return '<img src="'.showFileUrl($data[$option['name']]).'">';
        }
        $view = new View();
        $view->assign('image', $image);
        $content = $view->fetch(__DIR__ . '/pictures.html');
        return $content;
    }
}