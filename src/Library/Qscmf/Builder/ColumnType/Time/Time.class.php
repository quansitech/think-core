<?php
namespace Qscmf\Builder\ColumnType\Time;

use Qscmf\Builder\ColumnType\ColumnType;

class Time extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return qsEmpty($data[$option['name']]) ? '' : time_format($data[$option['name']], $option['value'] ?:'Y-m-d H:i:s');
    }
}