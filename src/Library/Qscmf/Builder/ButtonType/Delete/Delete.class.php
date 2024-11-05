<?php

namespace Qscmf\Builder\ButtonType\Delete;

use AntdAdmin\Component\Table\ActionType\BaseAction;
use AntdAdmin\Component\Table\ActionType\Button;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableButton;
use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class Delete extends ButtonType implements IAntdTableButton
{

    public function build(array &$option, ListBuilder $listBuilder)
    {
        $my_attribute['title'] = '删除';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-danger ajax-post confirm';
        $my_attribute['href'] = U(
            '/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/delete'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : []);

        return '';
    }

    public function tableButtonAntdRender($options, $listBuilder): BaseAction
    {
        $btn = new Button('删除');
        $btn->relateSelection()
            ->setProps([
                'danger' => true,
            ])
            ->request('delete', U('delete'), ['ids' => '__id__'], null, '确定删除？');
        return $btn;
    }
}