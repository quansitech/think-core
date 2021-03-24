<?php

namespace Qscmf\Builder\ColumnType\Num;


use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Num extends ColumnType implements EditableInterface
{
    public function build(array &$option, array $data, $listBuilder)
    {
        return $data[$option['name']];
    }

    public function editBuild(&$option, $data, $listBuilder){
        return "<input class='save' type='number' name='{$option['name']}[]' value={$data[$option['name']]} />";
    }

}