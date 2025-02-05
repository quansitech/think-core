<?php
namespace Qscmf\Builder\ColumnType\Status;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Select;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Lib\DBCont;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableColumn;

class Status extends ColumnType implements IAntdTableColumn
{

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

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $col = new Select($options['name'], $options['title']);
        $col->setValueEnum([
            DBCont::NORMAL_STATUS => ['text' => '正常', 'status' => 'Success'],
            DBCont::FORBIDDEN_STATUS => ['text' => '禁用', 'status' => 'Warning'],
        ]);
        return $col;
    }
}