<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\ListAdapter;

use AntdAdmin\Component\Table\ColumnType\OptionType\BaseOption;

interface IAntdTableRightBtn
{
    public function tableRightBtnAntdRender($options, $listBuilder): BaseOption|array;
}