<?php


namespace Qscmf\Builder\ButtonType\Save;

class DefaultEditableColumn
{
    use TargetFormTrait;
    public function build($column, $data, $listBuilder){
        $class = 'form-control input text ' . $this->getSaveTargetForm();

        return "<input class='{$class}' type='text' name='{$column['name']}[]' value='{$data[$column['name']]}' />";
    }
}