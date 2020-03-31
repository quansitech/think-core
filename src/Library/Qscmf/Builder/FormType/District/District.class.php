<?php
namespace Qscmf\Builder\FormType\District;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class District implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/district.html');
        return $content;
    }
}