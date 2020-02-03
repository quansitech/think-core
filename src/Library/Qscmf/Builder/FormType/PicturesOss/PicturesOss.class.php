<?php
namespace Qscmf\Builder\FormType\PicturesOss;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class PicturesOss implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/pictures_oss.html');
        return $content;
    }
}