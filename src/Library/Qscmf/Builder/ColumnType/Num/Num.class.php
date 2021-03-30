<?php

namespace Qscmf\Builder\ColumnType\Num;


use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Num extends ColumnType implements EditableInterface
{
    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder)
    {
        return $data[$option['name']];
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input text ". $this->getTargetForm();
        return "<input class='{$class}' type='number' name='{$option['name']}[]' value={$data[$option['name']]} />";
    }

    public function getSaveTargetForm()
    {
        return $this->getTargetForm();
    }

}