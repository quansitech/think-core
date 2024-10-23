<?php
namespace Qscmf\Builder\ColumnType\Fun;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Text;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\ColumnType;

class Fun extends ColumnType implements IAntdTableColumn
{

    public function build(array &$option, array $data, $listBuilder){
        $re = '';
        if(preg_match('/(.+)->(.+)\((.+)\)/', $option['value'], $object_matches)){
            $object_matches[3] = str_replace('\'', '', $object_matches[3]);
            $object_matches[3] = str_replace('"', '', $object_matches[3]);

            $param_arr = $this->parseParams($data[$option['name']]??'', $data[$listBuilder->getTableDataListKey()]??'', $object_matches[3]);
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

            $func_param_arr = $this->parseParams($data[$option['name']]??'', $data[$listBuilder->getTableDataListKey()]??'', $func_matches[2]);
            $re = call_user_func_array($func_matches[1], $func_param_arr);
        }
        return $re;
    }

    protected function parseParams(string $data_id, string $id, string $params) : array{
        $param_arr = explode(',', $params);
        foreach($param_arr as &$vo){
            $vo = str_replace('__data_id__', $data_id, $vo);
            $vo = str_replace('__id__', $id, $vo);
        }
        return $param_arr;
    }

    public function tableAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $col = new Text($options['name'], $options['title']);
        foreach ($datalist as &$item) {
            $item[$options['name']] = $this->build($options, $item, $listBuilder);
        }

        return $col;
    }
}