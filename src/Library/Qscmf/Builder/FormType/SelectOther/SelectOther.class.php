<?php
namespace Qscmf\Builder\FormType\SelectOther;

use Qscmf\Builder\FormType\FormType;
use Think\View;

class SelectOther implements FormType {

    public function build(array $form_type){
        $view = new View();
        $form_type['data_other'] = $form_type['value']['other'];
        $form_type['value'] = $form_type['value']['value'];
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/select_other.html');
        return $content;
    }
}