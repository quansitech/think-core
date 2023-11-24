<?php


namespace Qscmf\Builder\ColumnType;


use Qscmf\Builder\BaseBuilder;
use Qscmf\Builder\SubTableBuilder;

interface EditableInterface
{
    public function editBuild(array &$option, array $data, $listBuilder);

    public function getSaveTargetForm(BaseBuilder | SubTableBuilder $builder):string;
}