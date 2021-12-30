<?php
namespace Qscmf\Builder\ColumnType\Textarea;

use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Textarea extends ColumnType implements EditableInterface{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        return "<textarea class='form-control input text' style='width: 100%;' disabled title='{$option['name']}'>{$data[$option['name']]}</textarea>";
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input text ". $this->getSaveTargetForm(). " {$option['extra_class']}";

        return "<div class='input-control'> <textarea class='{$class}' name='{$option['name']}[]' {$option['extra_attr']}>{$data[$option['name']]}</textarea> </div>";
    }
}