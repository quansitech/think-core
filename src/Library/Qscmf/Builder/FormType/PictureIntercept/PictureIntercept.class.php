<?php
namespace Qscmf\Builder\FormType\PictureIntercept;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class PictureIntercept implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/picture_intercept.html');
        return $content;
    }
}