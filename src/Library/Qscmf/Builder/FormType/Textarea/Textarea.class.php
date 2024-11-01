<?php
namespace Qscmf\Builder\FormType\Textarea;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Textarea implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/textarea_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/textarea.html');
        }
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Textarea($options['name'], $options['title']);
        return $column;
    }
}