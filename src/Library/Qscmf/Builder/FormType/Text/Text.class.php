<?php
namespace Qscmf\Builder\FormType\Text;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Text implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/text.html');
        return $content;
    }
}