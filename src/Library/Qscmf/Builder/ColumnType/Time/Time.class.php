<?php
namespace Qscmf\Builder\ColumnType\Time;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Text;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\ColumnType;

class Time extends ColumnType implements IAntdTableColumn
{

    public function build(array &$option, array $data, $listBuilder){
        return qsEmpty($data[$option['name']]) ? '' : time_format($data[$option['name']], $option['value'] ?:'Y-m-d H:i:s');
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