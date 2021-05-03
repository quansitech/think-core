<?php
namespace Qscmf\Builder\ColumnType\Text;

use Qscmf\Builder\ColumnType\ColumnType;

class Text extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return '<span title="' . $data[$option['name']] . '" >' . $data[$option['name']] . '</span>';
    }
}