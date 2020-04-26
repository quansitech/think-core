<?php
namespace Qscmf\Builder\ColumnType\Picture;

use Qscmf\Builder\ColumnType\ColumnType;

class Picture extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return '<img src="'.showFileUrl($data[$option['name']]).'">';
    }
}