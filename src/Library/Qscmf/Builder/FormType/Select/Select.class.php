<?php
namespace Qscmf\Builder\FormType\Select;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Select implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/select.html');
        return $content;
    }
}