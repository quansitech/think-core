<?php
namespace Qscmf\Builder\FormType\Date;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Date implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/date.html');
        return $content;
    }
}