<?php
namespace Qscmf\Builder\FormType\Address;

use Qscmf\Builder\FormType\FormType;

class Address implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/ueditor.html');
        return $content;
    }
}