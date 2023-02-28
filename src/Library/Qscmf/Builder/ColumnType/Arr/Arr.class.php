<?php
namespace Qscmf\Builder\ColumnType\Arr;

use Qscmf\Builder\ColumnType\ColumnType;

class Arr extends ColumnType{

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
}