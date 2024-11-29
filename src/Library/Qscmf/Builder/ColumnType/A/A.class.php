<?php
namespace Qscmf\Builder\ColumnType\A;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Link;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\ColumnType;

class A extends ColumnType implements IAntdTableColumn
{

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

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $col = new Link($options['name'], $options['title']);

        $col->setFieldProps($options['value']);

        return $col;
    }
}