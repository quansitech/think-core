<?php
namespace Qscmf\Builder\ColumnType\Datetime;

use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\Date\Date;

class Datetime extends Date implements IAntdTableColumn
{
    protected string $_template =  __DIR__ . '/datetime.html';
    protected string $_default_format =  'Y-m-d H:i:s';

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): \AntdAdmin\Component\ColumnType\BaseColumn
    {
        foreach ($datalist as &$item) {
            $item[$options['name']] = $this->formatDateVal($item[$options['name']], $options['value']);
        }
        $col = new \AntdAdmin\Component\ColumnType\Datetime($options['name'], $options['title']);
        return $col;
    }
}