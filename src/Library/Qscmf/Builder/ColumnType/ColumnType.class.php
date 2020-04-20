<?php
namespace Qscmf\Builder\ColumnType;

abstract class ColumnType{

    public abstract function build(array &$option, array $data, $listBuilder);

}