<?php
namespace Qscmf\Builder\FormType\Province;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Province implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/province.html');
        return $content;
    }
}