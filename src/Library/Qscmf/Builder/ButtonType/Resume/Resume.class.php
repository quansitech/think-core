<?php
namespace Qscmf\Builder\ButtonType\Resume;

use AntdAdmin\Component\Table\ActionType\BaseAction;
use AntdAdmin\Component\Table\ActionType\Button;
use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableButton;

class Resume extends ButtonType implements IAntdTableButton
{

    public function build(array &$option, ListBuilder $listBuilder){
        $my_attribute['title'] = '启用';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-success ajax-post confirm';
        $my_attribute['href']  = U(
            '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/resume'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }

    public function tableButtonAntdRender($options, $listBuilder): BaseAction|array
    {
        $btn = new Button('启用');
        $btn->relateSelection()
            ->request('put', U('resume'), ['ids' => '__id__']);
        return $btn;
    }
}