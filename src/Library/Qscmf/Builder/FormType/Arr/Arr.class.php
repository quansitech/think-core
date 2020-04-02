<?php
namespace Qscmf\Builder\FormType\Arr;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Arr implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/array.html');
        return $content;
    }
}