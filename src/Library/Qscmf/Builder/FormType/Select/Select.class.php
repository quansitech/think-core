<?php
namespace Qscmf\Builder\FormType\Select;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\FormType\FormType;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Think\View;

class Select implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/select_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/select.html');
        }
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Select($options['name'], $options['title']);
        $column->setValueEnum($options['options']);
        return $column;
    }
}