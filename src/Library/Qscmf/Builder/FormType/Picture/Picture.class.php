<?php
namespace Qscmf\Builder\FormType\Picture;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Picture implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/picture.html');
        return $content;
    }
}