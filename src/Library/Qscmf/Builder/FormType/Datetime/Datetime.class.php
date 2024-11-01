<?php
namespace Qscmf\Builder\FormType\Datetime;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Datetime implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        if(!$this->isNumber($form_type['value']) && $form_type['value']){
            $form_type['value'] = strtotime($form_type['value']);
        }

        $form_type['options'] = (array)$form_type['options'];
        $opt = $form_type['options'];

        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $view->assign('opt', $opt);
        $content = $view->fetch(__DIR__ . '/datetime.html');
        return $content;
    }

    protected function isNumber($string) {
        return preg_match('/^[+-]?(\d+|\d*\.\d+)$/', $string) === 1;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\DateTime($options['name'], $options['title']);
        return $col;
    }
}