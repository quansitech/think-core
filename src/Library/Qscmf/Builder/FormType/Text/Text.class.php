<?php
namespace Qscmf\Builder\FormType\Text;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class Text implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/text_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/text.html');
        }
        return $content;
    }
}