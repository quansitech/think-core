<?php
namespace Qscmf\Builder\FormType\Self_;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Self_ implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/self.html');
        return $content;
    }
}