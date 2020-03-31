<?php
namespace Qscmf\Builder\FormType\Num;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Num implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/num.html');
        return $content;
    }
}