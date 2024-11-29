<?php
namespace Qscmf\Builder\ColumnType\Textarea;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Textarea extends ColumnType implements EditableInterface, IAntdTableColumn
{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        return "<textarea class='form-control input text' style='width: 100%;' disabled title='{$option['name']}'>{$data[$option['name']]}</textarea>";
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input text ". $this->getSaveTargetForm($listBuilder). " {$option['extra_class']}";
        $name = $this->buildName($option, $listBuilder);

        return "<div class='input-control'> <textarea class='{$class}' name='{$name}' {$option['extra_attr']}>{$data[$option['name']]}</textarea> </div>";
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        return new \AntdAdmin\Component\ColumnType\Textarea($options['name'], $options['title']);
    }
}