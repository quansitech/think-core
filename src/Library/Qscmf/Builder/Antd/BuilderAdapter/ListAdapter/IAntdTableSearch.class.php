<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\ListAdapter;

use AntdAdmin\Component\ColumnType\BaseColumn;

interface IAntdTableSearch
{
    public function tableSearchAntdRender($options, $listBuilder): BaseColumn|array;
}