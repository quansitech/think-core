<?php
namespace Qscmf\Builder\ButtonType\Save;

use AntdAdmin\Component\Table\ActionType\BaseAction;
use AntdAdmin\Component\Table\ActionType\StartEditable;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableButton;
use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class Save extends ButtonType implements IAntdTableButton
{

    public function build(array &$option, ListBuilder $listBuilder){
        $my_attribute['title'] = '保存';
        $my_attribute['target-form'] = $listBuilder?->getGid();
        $my_attribute['class'] = 'btn btn-primary ajax-post confirm';
        $my_attribute['href']  = U(
            '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/save'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }

    public function tableButtonAntdRender($options, $listBuilder): BaseAction
    {
        $btn = new StartEditable('编辑');
        $btn->saveRequest('put', U('save'));
        return $btn;
    }
}