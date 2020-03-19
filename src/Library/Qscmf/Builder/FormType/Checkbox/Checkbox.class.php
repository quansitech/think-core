<?php
namespace Qscmf\Builder\FormType\Checkbox;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Checkbox implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/checkbox.html');
        return $content;
    }
}