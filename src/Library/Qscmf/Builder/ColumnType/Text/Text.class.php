<?php
namespace Qscmf\Builder\ColumnType\Text;

use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableColumn;

class Text extends ColumnType implements EditableInterface, IAntdTableColumn
{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        return '<span title="' . $data[$option['name']] . '" >' . $data[$option['name']] . '</span>';
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input text ". $this->getSaveTargetForm($listBuilder). " {$option['extra_class']}";;
        $name = $this->buildName($option, $listBuilder);

        return "<input class='{$class}'  {$option['extra_attr']} type='text' name='$name' value='{$data[$option['name']]}' />";
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): \AntdAdmin\Component\ColumnType\BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\Text($options['name'], $options['title']);

        return $col;
    }
}