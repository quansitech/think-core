<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\FormAdapter;

use AntdAdmin\Component\ColumnType\BaseColumn;

interface IAntdFormItem
{
    public function formAntdRender($options): BaseColumn;
}