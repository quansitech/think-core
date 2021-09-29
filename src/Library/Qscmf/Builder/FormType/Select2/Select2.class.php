<?php
namespace Qscmf\Builder\FormType\Select2;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Select2 implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('gid', \Illuminate\Support\Str::uuid()->toString());
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/select2_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/select2.html');
        }
        return $content;
    }
}