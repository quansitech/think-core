<?php
namespace Qscmf\Builder\ListRightButton\Edit;

use Qscmf\Builder\ListRightButton\ListRightButton;

class Edit extends ListRightButton{

    public function build(array &$option, array $data, $listBuilder){
        $my_attribute['title'] = '编辑';
        $my_attribute['class'] = 'primary';
        $my_attribute['href']  = U(
            MODULE_NAME.'/'.CONTROLLER_NAME.'/edit',
            array($listBuilder->getTableDataListKey() => '__data_id__')
        );

        $option['attribute'] = $listBuilder->mergeAttr($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );
        return '';
    }
}