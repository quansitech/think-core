<?php
namespace Qscmf\Builder\ColumnType;

use Qscmf\Builder\SubTableBuilder;
abstract class ColumnType{

    public abstract function build(array &$option, array $data, $listBuilder);

    public function getIndex($listBuilder):int{
        return $listBuilder->getIndex();
    }

    public function buildName($option, $listBuilder):string{
        if ($listBuilder instanceof SubTableBuilder
            && $listBuilder->getNeedValidate()){
            return $option['name'].'['.$listBuilder->getIndex().']';
        }

        return $option['name'].'[]';
    }

}