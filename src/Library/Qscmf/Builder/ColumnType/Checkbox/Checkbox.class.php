<?php

namespace Qscmf\Builder\ColumnType\Checkbox;

use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Checkbox extends ColumnType implements EditableInterface{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        $checked = !qsEmpty($data[$option['name']], false);

        return '<input type="checkbox" disabled '. ($checked ? 'checked':'') .'/>';
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "input ". $this->getSaveTargetForm(). " {$option['extra_class']}";
        $checked = !qsEmpty($data[$option['name']], false);

        return "<input type='checkbox' onchange='$(this).next().val(this.checked);' class='{$class}' ".($checked ? 'checked':'')."  {$option['extra_attr']}/>
 <input type='hidden' name='{$option['name']}[]' class='{$class}' value='{$checked}' {$option['extra_attr']} />";
    }

}