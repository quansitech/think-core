<?php
namespace Qscmf\Builder\FormType\PictureIntercept;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class PictureIntercept implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('cacl_file_hash', $form_type["options"]['cacl_file_hash']??1);
        $content = $view->fetch(__DIR__ . '/picture_intercept.html');
        return $content;
    }
}