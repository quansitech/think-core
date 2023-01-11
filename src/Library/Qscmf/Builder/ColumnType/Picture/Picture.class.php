<?php
namespace Qscmf\Builder\ColumnType\Picture;

use Qscmf\Builder\ColumnType\ColumnType;
use Think\View;

class Picture extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        $view = new View();
        $image = [
            'url' => showFileUrl($data[$option['name']]),
        ];
        if(isset($option['value']) && $option['value']['small-url'] instanceof \Closure){
            $image['small_url'] = call_user_func($option['value']['small-url'], $data[$option['name']]);
        }
        else{
            $image['small_url'] = $image['url'];
        }
        $view->assign('image', $image);
        $content = $view->fetch(__DIR__ . '/picture.html');
        return $content;
    }
}