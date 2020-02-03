<?php
namespace Qscmf\Builder\FormType\AudioOss;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class AudioOss implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/audio_oss.html');
        return $content;
    }
}