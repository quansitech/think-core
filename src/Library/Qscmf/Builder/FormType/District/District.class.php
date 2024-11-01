<?php
namespace Qscmf\Builder\FormType\District;

use AntdAdmin\Component\ColumnType\Area;
use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class District implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        if(!is_array($form_type['options'])){
            $form_type['options'] = [];
        }

        $view = new View();
        $view->assign('gid', \Illuminate\Support\Str::uuid()->toString());
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/district_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/district.html');
        }
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $col = new Area($options['name'], $options['title']);

        return $col;
    }
}