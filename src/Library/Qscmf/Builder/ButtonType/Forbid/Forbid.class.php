<?php

namespace Qscmf\Builder\ButtonType\Forbid;

use AntdAdmin\Component\Table\ActionType\BaseAction;
use AntdAdmin\Component\Table\ActionType\Button;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableButton;
use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class Forbid extends ButtonType implements IAntdTableButton
{

    public function build(array &$option, ListBuilder $listBuilder)
    {
        $my_attribute['title'] = '禁用';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-warning ajax-post confirm';
        $my_attribute['href'] = U(
            '/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/forbid'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : []);

        return '';
    }

    public function tableButtonAntdRender($options, $listBuilder): BaseAction
    {
        $btn = new Button('禁用');
        $btn->relateSelection()
            ->request('put', U('forbid'), ['ids' => '__id__']);
        return $btn;
    }
}