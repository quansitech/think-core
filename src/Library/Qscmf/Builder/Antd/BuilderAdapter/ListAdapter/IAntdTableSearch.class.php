<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\ListAdapter;

use AntdAdmin\Component\ColumnType\BaseColumn;

interface IAntdTableSearch
{
    public function tableAntdRender($options, $listBuilder): BaseColumn|array;
}