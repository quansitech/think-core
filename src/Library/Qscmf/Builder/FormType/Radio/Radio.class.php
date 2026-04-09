<?php
namespace Qscmf\Builder\FormType\Radio;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\RadioButton;
use Qscmf\Builder\FormType\FormType;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Think\View;

class Radio implements FormType, IAntdFormColumn {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/radio.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $column = new RadioButton($options['name'], $options['title']);
        $column->setValueEnum($options['options']);
        return $column;
    }
}
