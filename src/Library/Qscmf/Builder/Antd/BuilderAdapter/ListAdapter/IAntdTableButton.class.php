<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\ListAdapter;

use AntdAdmin\Component\Table\ActionType\BaseAction;

interface IAntdTableButton
{
    public function tableAntdRender($options, $listBuilder): BaseAction|array;
}