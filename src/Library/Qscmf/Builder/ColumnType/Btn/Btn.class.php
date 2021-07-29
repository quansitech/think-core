<?php
namespace Qscmf\Builder\ColumnType\Btn;

use Qscmf\Builder\ColumnType\ColumnType;

class Btn extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return $data[$option['name']];
    }
}