<?php
namespace Qscmf\Builder\FormType\Address;

use AntdAdmin\Component\ColumnType\Area;
use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Address implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/address.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $col = new Area($options['name'], $options['title']);
        return $col;
    }
}