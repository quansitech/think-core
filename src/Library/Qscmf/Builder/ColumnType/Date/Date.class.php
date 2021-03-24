<?php
namespace Qscmf\Builder\ColumnType\Date;

use Qscmf\Builder\ColumnType\ColumnType;

class Date extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return time_format($data[$option['name']], $option['value'] ?:'Y-m-d');
    }
}