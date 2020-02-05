<?php
namespace Qscmf\Builder\ButtonType\Delete;

use Qscmf\Builder\ButtonType\ButtonType;

class Delete extends ButtonType{

    public function build(array &$option){
        $my_attribute['title'] = '删除';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-danger ajax-post confirm';
        $my_attribute['href']  = U(
            '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/delete'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }
}