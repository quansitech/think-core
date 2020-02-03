<?php
namespace Qscmf\Builder\FormType\PicturesIntercept;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class PicturesIntercept implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/pictures_intercept.html');
        return $content;
    }
}