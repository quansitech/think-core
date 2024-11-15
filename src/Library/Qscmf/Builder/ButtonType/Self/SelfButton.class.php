<?php

namespace Qscmf\Builder\ButtonType\Self;

use AntdAdmin\Component\Table\ActionType\BaseAction;
use AntdAdmin\Component\Table\ActionType\Button;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableButton;
use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class SelfButton extends ButtonType implements IAntdTableButton
{

    public function build(array &$option, ListBuilder $listBuilder)
    {
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-danger';

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : []);

        return '';
    }

    public function tableButtonAntdRender($options, $listBuilder): BaseAction
    {
        $btn = new Button($options['attribute']['title']);
        $confirm = str_contains($options['attribute']['class'] ?? '', 'confirm') ?? false;
        if (str_contains($options['attribute']['class'] ?? '', 'ajax-get')) {
            $btn->request('get', $options['attribute']['href'], null, null, $confirm ? '确认操作？' : null);
        } elseif (str_contains($options['attribute']['class'] ?? '', 'ajax-post')) {
            $btn->request('post', $options['attribute']['href'], null, null, $confirm ? '确认操作？' : null);
        } elseif ($options['attribute']['href']) {
            $btn->link($options['attribute']['href']);
        }
        return $btn;
    }
}