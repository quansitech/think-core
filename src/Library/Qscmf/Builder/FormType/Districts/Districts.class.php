<?php
namespace Qscmf\Builder\FormType\Districts;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Districts implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/districts.html');
        return $content;
    }
}