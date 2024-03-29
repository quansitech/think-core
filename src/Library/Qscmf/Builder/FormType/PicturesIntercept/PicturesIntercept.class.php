<?php
namespace Qscmf\Builder\FormType\PicturesIntercept;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class PicturesIntercept implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('cacl_file_hash', $form_type["options"]['cacl_file_hash']??1);
        $content = $view->fetch(__DIR__ . '/pictures_intercept.html');
        return $content;
    }
}