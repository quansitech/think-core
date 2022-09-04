<?php
namespace Qscmf\Builder\FormType\File;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FileFormType;
use Qscmf\Builder\FormType\FormType;
use Qscmf\Builder\FormType\TUploadConfig;
use Think\View;

class File extends FileFormType implements FormType {
    use TUploadConfig;

    public function build(array $form_type){
        $upload_type = $this->genUploadConfigCls($form_type['extra_attr'], 'file');
        $view = new View();

        if($form_type['value']){
            $file = [];
            $file['url'] = showFileUrl($form_type['value']);
            if($this->needPreview($file['url'])){
                $file['preview_url'] = $this->genPreviewUrl($file['url']);
            }
            $view->assign('file', $file);
        }

        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $view->assign('file_ext',  $upload_type->getExts());
        $view->assign('file_max_size',  $upload_type->getMaxSize());
        $view->assign('js_fn', $this->buildJsFn());
        $content = $view->fetch(__DIR__ . '/file.html');
        return $content;
    }
}