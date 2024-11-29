<?php

namespace Qscmf\Builder\ColumnType\Select;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Select extends ColumnType implements EditableInterface, IAntdTableColumn
{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder)
    {
        $text = $option['value'][$data[$option['name']]];
        return '<span title="' . $text . '" >' . $text . '</span>';
    }

    public function editBuild(&$option, $data, $listBuilder)
    {
        $class = "form-control input " . $this->getSaveTargetForm($listBuilder) . " {$option['extra_class']}";

        $view = new \Think\View();
        $view->assign('gid', Str::uuid());
        $view->assign('options', $option);
        $view->assign('data', $data);
        $view->assign('class', $class);
        $view->assign('value', $data[$option['name']]);
        $view->assign('name', $this->buildName($option, $listBuilder));

        return $view->fetch(__DIR__ . '/select.html');
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\Select($options['name'], $options['title']);

        $col->setValueEnum($options['value']);
        return $col;
    }
}