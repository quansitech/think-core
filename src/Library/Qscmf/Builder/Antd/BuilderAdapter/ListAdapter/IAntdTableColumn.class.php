<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\ListAdapter;

use AntdAdmin\Component\ColumnType\BaseColumn;

interface IAntdTableColumn
{
    public function tableAntdRender($options, &$datalist, $listBuilder): BaseColumn;
}