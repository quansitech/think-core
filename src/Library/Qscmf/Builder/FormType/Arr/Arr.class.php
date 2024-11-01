<?php
namespace Qscmf\Builder\FormType\Arr;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Textarea;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Arr implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/array.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $column = new Textarea($options['name'], $options['title']);
        return $column;
    }
}