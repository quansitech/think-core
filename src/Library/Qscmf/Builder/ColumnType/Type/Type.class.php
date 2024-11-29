<?php
namespace Qscmf\Builder\ColumnType\Type;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Select;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\ColumnType;

class Type extends ColumnType implements IAntdTableColumn
{

    public function build(array &$option, array $data, $listBuilder){
        $form_item_type = C('FORM_ITEM_TYPE');
        return $form_item_type[$data[$option['name']]][0];
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $col = new Select($options['name'], $options['title']);
        $col->setValueEnum(C('FORM_ITEM_TYPE'));
        return $col;
    }
}