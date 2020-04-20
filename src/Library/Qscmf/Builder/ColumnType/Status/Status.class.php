<?php
namespace Qscmf\Builder\ColumnType\Status;

use Qscmf\Builder\ColumnType\ColumnType;

class Status extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        $re = '';
        switch($data[$option['name']]){
            case '0':
                $re = '<i class="fa fa-ban text-danger"></i>';
                break;
            case '1':
                $re = '<i class="fa fa-check text-success"></i>';
                break;
        }
        return $re;
    }
}