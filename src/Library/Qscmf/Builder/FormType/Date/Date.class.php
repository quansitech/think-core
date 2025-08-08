<?php
namespace Qscmf\Builder\FormType\Date;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Date implements FormType {

    public function build(array $form_type){
        if(!$this->isNumber($form_type['value']) && $form_type['value']){
            $form_type['value'] = strtotime($form_type['value']);
        }

        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/date_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/date.html');
        }
        return $content;
    }

    protected function isNumber($string) {
        return preg_match('/^[+-]?(\d+|\d*\.\d+)$/', $string) === 1;
    }
}