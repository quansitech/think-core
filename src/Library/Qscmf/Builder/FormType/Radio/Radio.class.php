<?php
namespace Qscmf\Builder\FormType\Radio;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Radio implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/radio.html');
        return $content;
    }
}