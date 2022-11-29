<?php
namespace Qscmf\Builder\FormType\Files;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FileFormType;
use Qscmf\Builder\FormType\FormType;
use Think\View;
use Qscmf\Builder\FormType\TUploadConfig;

class Files extends FileFormType implements FormType {

    use TUploadConfig;

    public function build(array $form_type){
        $upload_type = $this->genUploadConfigCls($form_type['extra_attr'], 'file');

        $view = new View();

        if($form_type['value']){
            $files = [];
            foreach(explode(',', $form_type['value']) as $file_id){
                $data = [];
                $data['url'] = showFileUrl($file_id);

                $data['id'] = $file_id;
                if($this->needPreview($data['url'])){
                    $data['preview_url'] = $this->genPreviewUrl($data['url']);
                }
                $files[] = $data;
            }

            $view->assign('files', $files);
        }

        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $view->assign('file_ext',  $upload_type->getExts());
        $view->assign('file_max_size',  $upload_type->getMaxSize());
        $view->assign('js_fn', $this->buildJsFn());
        $content = $view->fetch(__DIR__ . '/files.html');
        return $content;
    }
}