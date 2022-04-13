<?php
namespace Qscmf\Builder\ColumnType\Picture;

use Qscmf\Builder\ColumnType\ColumnType;
use Think\View;

class Picture extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        // 如果value值存在，则使用viewerjs
        // value 可接收数组参数['width'=>200,'height'=>200,'type'=>'m_fill']，或者直接传入完整拼接字段['cut'=>'?x-oss-process=image/resize,m_fill,w_200,h_200']
        // oss图片裁剪文档 https://help.aliyun.com/document_detail/44688.html?spm=a2c4g.11186623.6.1366.1fb817f1R3GdJl
        if ($option['value']){
            $value = is_array($option['value'])? $option['value'] : array($option['value']);
            if($value['cut']){
                $cut = $value['cut'];
            }else{
                $width = $value['width']? $value['width'] : 100;
                $height = $value['height']? $value['height'] : 100;
                $type = $value['type']? $value['type'] : 'm_fill';
                $cut = '?x-oss-process=image/resize,'.$type.',w_'.$width.',h_'.$height;
            }
            $image['original'] = showFileUrl($data[$option['name']]); // 原图
            $image['src'] = showFileUrl($data[$option['name']]).$cut; // 缩略图
        }else{
            return '<img src="'.showFileUrl($data[$option['name']]).'">';
        }
        $view = new View();
        $view->assign('image', $image);
        $content = $view->fetch(__DIR__ . '/picture.html');
        return $content;
    }
}