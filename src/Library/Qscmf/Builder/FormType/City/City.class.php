<?php
namespace Qscmf\Builder\FormType\City;

use AntdAdmin\Component\ColumnType\Area;
use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\FormType\FormType;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Think\View;

class City implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/city.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $col = new Area($options['name'], $options['title']);
        return $col;
    }
}