<?php
namespace Qscmf\Builder\FormType\Text;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Text implements FormType, IAntdFormItem
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/text_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/text.html');
        }
        return $content;
    }

    public function formAntdRender($options): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Text($options['name'], $options['title']);

        return $column;
    }
}