<?php
namespace Qscmf\Builder\ColumnType\A;

use Qscmf\Builder\ColumnType\ColumnType;

class A extends ColumnType {

    public function build(array &$option, array $data, $listBuilder){
        return '<a ' . $this->compileHtmlAttr($option['value']) . ' >' . $data[$option['name']] . '</a>';
    }

    protected function compileHtmlAttr($attr) {
        $result = array();
        foreach ($attr as $key => $value) {
            if(!empty($value) && !is_array($value)){
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
        }
        $result = implode(' ', $result);
        return $result;
    }
}