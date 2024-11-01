<?php
namespace Qscmf\Builder\ButtonType\Addnew;

use AntdAdmin\Component\Table\ActionType\BaseAction;
use AntdAdmin\Component\Table\ActionType\Button;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableButton;
use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class Addnew extends ButtonType implements IAntdTableButton
{

    public function build(array &$option, ListBuilder $listBuilder){
        $my_attribute['title'] = '新增';
        $my_attribute['class'] = 'btn btn-primary';
        $my_attribute['href']  = U(MODULE_NAME.'/'.CONTROLLER_NAME.'/add');

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }

    public function tableButtonAntdRender($options, $listBuilder): BaseAction
    {
        $btn = new Button($options['attribute']['title'] ?? '新增');
        $btn->link(U('add'));
        $btn->setProps(['type' => 'primary']);
        return $btn;
    }
}