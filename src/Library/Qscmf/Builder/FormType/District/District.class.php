<?php
namespace Qscmf\Builder\FormType\District;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class District implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('gid', \Illuminate\Support\Str::uuid()->toString());
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/district_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/district.html');
        }
        return $content;
    }
}