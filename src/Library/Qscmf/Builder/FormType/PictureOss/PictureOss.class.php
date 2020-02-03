<?php
namespace Qscmf\Builder\FormType\PictureOss;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class PictureOss implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/picture_oss.html');
        return $content;
    }
}