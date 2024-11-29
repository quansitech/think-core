<?php

namespace Qscmf\Builder\ColumnType\Checkbox;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Checkbox extends ColumnType implements EditableInterface, IAntdTableColumn
{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        $checked = !qsEmpty($data[$option['name']], false);

        return '<input type="checkbox" disabled '. ($checked ? 'checked':'') .'/>';
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "input ". $this->getSaveTargetForm($listBuilder). " {$option['extra_class']}";
        $checked = !qsEmpty($data[$option['name']], false);
        $name = $this->buildName($option, $listBuilder);

        return "<input type='checkbox' onchange='$(this).next().val(this.checked);' class='{$class}' ".($checked ? 'checked':'')."  {$option['extra_attr']}/>
 <input type='hidden' name='{$name}' class='{$class}' value='{$checked}' {$option['extra_attr']} />";
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Checkbox($options['name'], $options['title']);
        foreach ($datalist as &$item) {
            $item[$options['name']] = !qsEmpty($item[$options['name']], false);
        }

        $column->setValueEnum([
            true => ''
        ]);
        return $column;
    }
}