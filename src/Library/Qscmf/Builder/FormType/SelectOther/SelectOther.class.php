<?php
namespace Qscmf\Builder\FormType\SelectOther;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class SelectOther implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/select_other.html');
        return $content;
    }
}