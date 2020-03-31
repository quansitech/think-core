<?php
namespace Qscmf\Builder\FormType\Hidden;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Hidden implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/hidden.html');
        return $content;
    }
}