<?php

namespace Qscmf\Builder\Antd\BuilderAdapter\FormAdapter;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\Form\ActionType\BaseAction;

interface IAntdFormButton
{
    public function formAntdRender($options): BaseAction;
}