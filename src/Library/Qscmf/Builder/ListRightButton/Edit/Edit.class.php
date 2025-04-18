<?php
namespace Qscmf\Builder\ListRightButton\Edit;

use AntdAdmin\Component\Table\ColumnType\ActionType\BaseAction;
use AntdAdmin\Component\Table\ColumnType\ActionType\Link;
use Qscmf\Builder\ListRightButton\ListRightButton;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableRightBtn;

class Edit extends ListRightButton implements IAntdTableRightBtn
{

    public function build(array &$option, array $data, $listBuilder){
        $my_attribute['title'] = '编辑';
        $my_attribute['class'] = 'primary';
        $my_attribute['href']  = U(
            MODULE_NAME.'/'.CONTROLLER_NAME.'/edit',
            array($listBuilder->getDataKeyName() => '__data_id__')
        );

        $option['attribute'] = $listBuilder->mergeAttr($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );
        return '';
    }

    public function tableRightBtnAntdRender($options, $listBuilder): BaseAction
    {
        $link = new Link('编辑');
        return $link->setHref(U('edit', ['id' => '__id__']));
    }
}