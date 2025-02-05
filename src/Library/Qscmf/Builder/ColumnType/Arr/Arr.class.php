<?php
namespace Qscmf\Builder\ColumnType\Arr;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\ColumnType\ColumnType;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableColumn;

class Arr extends ColumnType implements IAntdTableColumn
{

    public function build(array &$option, array $data, $listBuilder){
        $arr = explode(',', $data[$option['name']]);

        $html = '';

        foreach($arr as $vo){
            $html .=<<<html
    <div style="display:flex;">
        <span>{$vo}</span>
    </div>
html;

        }


        return $html;
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        return new \AntdAdmin\Component\ColumnType\Text($options['name'], $options['title']);
    }
}