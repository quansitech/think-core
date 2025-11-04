<?php
namespace Qscmf\Builder\FormType\Num;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Num implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/num_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/num.html');
        }
        return $content;
    }
}