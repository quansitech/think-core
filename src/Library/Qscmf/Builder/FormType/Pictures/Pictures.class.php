<?php
namespace Qscmf\Builder\FormType\Pictures;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Pictures implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/pictures_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/pictures.html');
        }
        return $content;
    }
}