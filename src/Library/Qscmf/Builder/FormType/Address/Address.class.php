<?php
namespace Qscmf\Builder\FormType\Address;

use Qscmf\Builder\FormType\FormType;
use Think\View;
use Illuminate\Support\Str;

class Address implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/address.html');
        return $content;
    }
}