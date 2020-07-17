<?php
namespace Qscmf\Builder\FormType\Ueditor;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Ueditor implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        if(C('CUSTOM_UEDITOR_JS_CONFIG')){
            $view->assign('configJs', C('CUSTOM_UEDITOR_JS_CONFIG'));
            $view->assign('home_url', __ROOT__ . '/Public/libs/ueditor/');
        }
        else{
            $view->assign('configJs', __ROOT__ . '/Public/libs/ueditor/ueditor.config.js');
        }
        $content = $view->fetch(__DIR__ . '/ueditor.html');
        
        return $content;
    }
}