<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\ListAdapter;

use AntdAdmin\Component\Table\ColumnType\ActionType\BaseAction;

interface IAntdTableRightBtn
{
    public function tableRightBtnAntdRender($options, $listBuilder): BaseAction|array;
}