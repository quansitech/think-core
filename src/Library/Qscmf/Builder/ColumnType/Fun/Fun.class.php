<?php
namespace Qscmf\Builder\ColumnType\Fun;

use Qscmf\Builder\ColumnType\ColumnType;

class Fun extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        $re = '';
        if(preg_match('/(.+)->(.+)\((.+)\)/', $option['value'], $object_matches)){
            $object_matches[3] = str_replace('\'', '', $object_matches[3]);
            $object_matches[3] = str_replace('"', '', $object_matches[3]);
            $object_matches[3] = str_replace('__data_id__', $data[$option['name']], $object_matches[3]);
            $object_matches[3] = str_replace('__id__', $data[$listBuilder->getTableDataListKey()], $object_matches[3]);
            $param_arr = explode(',', $object_matches[3]);
            if(preg_match('/(.+)\((.+)\)/', $object_matches[1], $object_func_matches)){
                $object_func_matches[2] = str_replace('\'', '', $object_func_matches[2]);
                $object_func_matches[2] = str_replace('"', '', $object_func_matches[2]);
                $object_param_arr = explode(',', $object_func_matches[2]);
                $object = call_user_func_array($object_func_matches[1], $object_param_arr);
                $re = call_user_func_array(array($object, $object_matches[2]), $param_arr);
            }
        }
        else if(preg_match('/(.+)\((.+)\)/', $option['value'], $func_matches)){
            $func_matches[2] = str_replace('\'', '', $func_matches[2]);
            $func_matches[2] = str_replace('"', '', $func_matches[2]);
            $func_matches[2] = str_replace('__data_id__', $data[$option['name']], $func_matches[2]);
            $func_matches[2] = str_replace('__id__', $data[$listBuilder->getTableDataListKey()], $func_matches[2]);
            $func_param_arr = explode(',', $func_matches[2]);
            $re = call_user_func_array($func_matches[1], $func_param_arr);
        }
        return $re;
    }
}