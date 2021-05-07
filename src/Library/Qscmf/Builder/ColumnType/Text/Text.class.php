<?php
namespace Qscmf\Builder\ColumnType\Text;

use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Text extends ColumnType implements EditableInterface{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        return '<span title="' . $data[$option['name']] . '" >' . $data[$option['name']] . '</span>';
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input text ". $this->getSaveTargetForm();
        return "<input class='{$class}' type='text' name='{$option['name']}[]' value={$data[$option['name']]} />";
    }
}