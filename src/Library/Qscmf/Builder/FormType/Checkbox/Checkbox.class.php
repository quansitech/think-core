<?php
namespace Qscmf\Builder\FormType\Checkbox;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Checkbox implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/checkbox.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\Checkbox($options['name'], $options['title']);

        $col->setValueEnum($options['options']);
        return $col;
    }
}