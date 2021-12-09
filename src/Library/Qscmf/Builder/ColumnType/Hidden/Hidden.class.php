<?php

namespace Qscmf\Builder\ColumnType\Hidden;

use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Hidden extends ColumnType implements EditableInterface{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        return '<span style="display: none" title="' . $data[$option['name']] . '" >' . $data[$option['name']] . '</span>';
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input text ". $this->getSaveTargetForm(). " {$option['extra_class']}";
        return "<input type='hidden' name='{$option['name']}[]' class='{$class}' value='{$data[$option['name']]}' {$option['extra_attr']} />";
    }
}