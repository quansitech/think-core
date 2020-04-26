<?php
namespace Qscmf\Builder\ColumnType\Icon;

use Qscmf\Builder\ColumnType\ColumnType;

class Icon extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return '<i class="'.$data[$option['name']].'"></i>';
    }
}