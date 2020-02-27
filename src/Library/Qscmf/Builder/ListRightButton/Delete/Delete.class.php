<?php
namespace Qscmf\Builder\ListRightButton\Delete;

use Qscmf\Builder\ListRightButton\ListRightButton;

class Delete extends ListRightButton {

    public function build(array &$option, array $data, $listBuilder){
        $my_attribute['title'] = '删除';
        $my_attribute['class'] = 'label label-danger ajax-get confirm';
        $my_attribute['href'] = U(
            MODULE_NAME.'/'.CONTROLLER_NAME.'/delete',
            array(
                'ids' => '__data_id__'
            )
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );
        return '';
    }
}