<?php
namespace Qscmf\Builder\ColumnType\Time;

use Qscmf\Builder\ColumnType\ColumnType;

class Time extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return time_format($data[$option['name']]);
    }
}