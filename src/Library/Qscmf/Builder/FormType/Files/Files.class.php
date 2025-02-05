<?php
namespace Qscmf\Builder\FormType\Files;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\File;
use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FileFormType;
use Qscmf\Builder\FormType\FormType;
use Qscmf\Builder\FormType\TUploadConfig;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Think\View;

class Files extends FileFormType implements FormType, IAntdFormColumn
{

    use TUploadConfig;

    public function build(array $form_type){
        $upload_type = $this->genUploadConfigCls($form_type['extra_attr'], 'file');

        $view = new View();

        if($form_type['value']){
            $files = [];
            foreach(explode(',', $form_type['value']) as $file_id){
                $data = [];
                $data['url'] = U('/qscmf/resource/download',['file_id'=>$file_id],'',true);
                $data['id'] = $file_id;
                if($this->needPreview(showFileUrl($file_id))){
                    $data['preview_url'] = $this->genPreviewUrl(showFileUrl($file_id));
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
        $view->assign('cate', $upload_type->getType());
        $view->assign('cacl_file_hash', $form_type["options"]['cacl_file_hash']??1);
        $content = $view->fetch(__DIR__ . '/files.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $col = new File($options['name'], $options['title']);
        return $col;
    }
}