<?php
namespace Qscmf\Builder\FormType\Arr;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Textarea;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Arr implements FormType, IAntdFormItem
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/array.html');
        return $content;
    }

    public function formAntdRender($options): BaseColumn
    {
        $column = new Textarea($options['name'], $options['title']);
        return $column;
    }
}