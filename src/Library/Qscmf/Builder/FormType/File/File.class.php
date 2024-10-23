<?php

namespace Qscmf\Builder\FormType\File;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FileFormType;
use Qscmf\Builder\FormType\FormType;
use Qscmf\Builder\FormType\TUploadConfig;
use Think\View;

class File extends FileFormType implements FormType, IAntdFormItem
{
    use TUploadConfig;

    public function build(array $form_type)
    {
        $upload_type = $this->genUploadConfigCls($form_type['extra_attr'], 'file');
        $view = new View();

        if ($form_type['value']) {
            $file = [];
            $file['url'] = U('/qscmf/resource/download', ['file_id' => $form_type['value']], '', true);
            if ($this->needPreview(showFileUrl($form_type['value']))) {
                $file['preview_url'] = $this->genPreviewUrl(showFileUrl($form_type['value']));
            }
            $view->assign('file', $file);
        }

        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $view->assign('file_ext', $upload_type->getExts());
        $view->assign('file_max_size', $upload_type->getMaxSize());
        $view->assign('js_fn', $this->buildJsFn());
        $view->assign('cate', $upload_type->getType());
        $view->assign('cacl_file_hash', $form_type["options"]['cacl_file_hash'] ?? 1);
        $content = $view->fetch(__DIR__ . '/file.html');
        return $content;
    }

    public function formAntdRender($options): BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\File($options['name'], $options['title']);
        return $col;
    }
}