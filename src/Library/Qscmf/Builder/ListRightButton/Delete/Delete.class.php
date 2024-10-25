<?php

namespace Qscmf\Builder\ListRightButton\Delete;

use AntdAdmin\Component\Table\ColumnType\OptionType\BaseOption;
use AntdAdmin\Component\Table\ColumnType\OptionType\Link;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableRightBtn;
use Qscmf\Builder\ListRightButton\ListRightButton;

class Delete extends ListRightButton implements IAntdTableRightBtn
{

    public function build(array &$option, array $data, $listBuilder)
    {
        $my_attribute['title'] = '删除';
        $my_attribute['class'] = 'danger ajax-get confirm';
        $my_attribute['href'] = U(
            MODULE_NAME . '/' . CONTROLLER_NAME . '/delete',
            array(
                'ids' => '__data_id__'
            )
        );

        $option['attribute'] = $listBuilder->mergeAttr($my_attribute, is_array($option['attribute']) ? $option['attribute'] : []);
        return '';
    }

    public function tableRightBtnAntdRender($options, $listBuilder): BaseOption
    {
        $link = new Link('删除');
        $link->setDanger(true)
            ->request('delete', U('delete'), ['ids' => '__id__'], null, '确定删除？');
        return $link;
    }
}