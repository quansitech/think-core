<?php
namespace Qscmf\Builder\ColumnType\Self;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Text;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\ColumnType;

class Self_ extends ColumnType implements IAntdTableColumn
{

    public function build(array &$option, array $data, $listBuilder){
        return $option['value'];
    }


    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        foreach ($datalist as &$item) {
            $item[$options['name']] = $options['value'];
        }
        $col = new Text($options['name'], $options['title']);
        return $col;
    }
}