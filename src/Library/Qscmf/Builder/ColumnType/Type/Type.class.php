<?php
namespace Qscmf\Builder\ColumnType\Type;

use Qscmf\Builder\ColumnType\ColumnType;

class Type extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        $form_item_type = C('FORM_ITEM_TYPE');
        return $form_item_type[$data[$option['name']]][0];
    }
}