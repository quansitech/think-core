<?php
namespace Qscmf\Builder\FormType\City;

use AntdAdmin\Component\ColumnType\Area;
use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class City implements FormType, IAntdFormItem
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/city.html');
        return $content;
    }

    public function formAntdRender($options): BaseColumn
    {
        $col = new Area($options['name'], $options['title']);
        return $col;
    }
}