<?php
namespace Qscmf\Builder\FormType\Num;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Num implements FormType, IAntdFormItem
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/num.html');
        return $content;
    }

    public function formAntdRender($options): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Text($options['name'], $options['title']);

        return $column;
    }
}