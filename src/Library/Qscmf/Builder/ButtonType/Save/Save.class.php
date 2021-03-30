<?php
namespace Qscmf\Builder\ButtonType\Save;

use Qscmf\Builder\ButtonType\ButtonType;

class Save extends ButtonType{

    const TARGET_FORM = 'save';

    public function build(array &$option){
        $my_attribute['title'] = '保存';
        $my_attribute['target-form'] = self::TARGET_FORM;
        $my_attribute['class'] = 'btn btn-primary ajax-post confirm';
        $my_attribute['href']  = U(
            '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/save'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }
}