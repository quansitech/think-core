<?php
namespace Qscmf\Builder\FormType\Datetime;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Datetime implements FormType {

    public function build(array $form_type){
        $form_type['options'] = (array)$form_type['options'];
        $opt = $form_type['options'];

        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $view->assign('opt', $opt);
        $content = $view->fetch(__DIR__ . '/datetime.html');
        return $content;
    }
}