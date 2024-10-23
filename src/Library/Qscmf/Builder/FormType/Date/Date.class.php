<?php
namespace Qscmf\Builder\FormType\Date;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormItem;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Date implements FormType, IAntdFormItem
{

    public function build(array $form_type){
        if(!$this->isNumber($form_type['value']) && $form_type['value']){
            $form_type['value'] = strtotime($form_type['value']);
        }

        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/date.html');
        return $content;
    }

    protected function isNumber($string) {
        return preg_match('/^[+-]?(\d+|\d*\.\d+)$/', $string) === 1;
    }

    public function formAntdRender($options): BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\Date($options['name'], $options['title']);
        return $col;
    }
}