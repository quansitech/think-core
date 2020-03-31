<?php
namespace Qscmf\Builder\FormType\Ueditor;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Ueditor implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/ueditor.html');
        return $content;
    }
}