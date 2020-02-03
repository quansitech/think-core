<?php
namespace Qscmf\Builder\FormType\Select2;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Select2 implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/select2.html');
        return $content;
    }
}