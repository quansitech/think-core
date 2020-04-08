<?php
namespace Qscmf\Builder\FormType\Self;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Self_ implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/self.html');
        return $content;
    }
}