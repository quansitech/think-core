<?php
namespace Qscmf\Builder\FormType\Pictures;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;
use Qscmf\Builder\FormType\TUploadConfig;

class Pictures implements FormType {
    use TUploadConfig;

    public function build(array $form_type){
        $upload_type = $this->genUploadConfigCls($form_type['extra_attr'], 'image');
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $view->assign('file_ext',  $upload_type->getExts());
        $view->assign('file_max_size',  $upload_type->getMaxSize());
        $view->assign('cate', $upload_type->getType());
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/pictures_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/pictures.html');
        }
        return $content;
    }
}