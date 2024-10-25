<?php

namespace Qscmf\Builder\ListRightButton\Self;

use AntdAdmin\Component\Form\ActionType\BaseAction;
use AntdAdmin\Component\Table\ColumnType\ActionType\BaseAction as TableColumnAction;
use AntdAdmin\Component\Table\ColumnType\ActionType\Link;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormButton;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableRightBtn;
use Qscmf\Builder\ListRightButton\ListRightButton;

class SelfButton extends ListRightButton implements IAntdTableRightBtn, IAntdFormButton
{

    public function build(array &$option, array $data, $listBuilder)
    {
        $my_attribute['class'] = 'default';

        $option['attribute'] = $listBuilder->mergeAttr($my_attribute, is_array($option['attribute']) ? $option['attribute'] : []);
        return '';
    }

    public function tableRightBtnAntdRender($options, $listBuilder): TableColumnAction
    {
        return new Link($options['attribute']['title']);
    }

    public function formButtonAntdRender($options): BaseAction
    {
        $btn = new \AntdAdmin\Component\Form\ActionType\Button($options['attribute']['title']);
        if ($options['attribute']['href']) {
            $btn->link($options['attribute']['href']);
        }
        return $btn;
    }
}