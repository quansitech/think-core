<?php
namespace Qscmf\Builder\FormType\Tags;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Tags implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/tags.html');
        return $content;
    }
}