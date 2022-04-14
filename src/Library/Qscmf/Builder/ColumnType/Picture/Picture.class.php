<?php
namespace Qscmf\Builder\ColumnType\Picture;

use Qscmf\Builder\ColumnType\ColumnType;
use Think\View;

class Picture extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        if ($value = $option['value']){
            $fun = str_replace('__data_id__', '"'.$data[$option['name']].'"', $value);
            $fun = str_replace('__id__', '"'.$data[$listBuilder->getTableDataListKey()].'"', $value);
            $src = eval("return $fun;");
            $image['src'] = $src; // 缩略图
        }else{
            $image['src'] = showFileUrl($data[$option['name']]).'?x-oss-process=image/resize,m_fill,w_100,h_100';
        }
        $image['original'] = showFileUrl($data[$option['name']]); // 原图
        $view = new View();
        $view->assign('image', $image);
        $content = $view->fetch(__DIR__ . '/column_picture.html');
        return $content;
    }
}