<?php
namespace Qscmf\Builder\FormType\Hidden;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Text;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Hidden implements FormType, IAntdFormItem
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/hidden.html');
        return $content;
    }

    public function formAntdRender($options): BaseColumn
    {
        $column = new Text($options['name'], $options['title']);

        $column->hideInForm();
        return $column;
    }
}