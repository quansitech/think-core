<?php


namespace Qscmf\Builder\ColumnType;


interface EditableInterface
{
    public function editBuild(array &$option, array $data, $listBuilder);

    public function getSaveTargetForm();
}