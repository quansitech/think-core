<?php
namespace Qscmf\Builder\ColumnType\Self;

use Qscmf\Builder\ColumnType\ColumnType;

class Self_ extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return $option['value'];
    }
}