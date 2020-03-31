<?php
namespace Qscmf\Builder\ButtonType\Resume;

use Qscmf\Builder\ButtonType\ButtonType;

class Resume extends ButtonType{

    public function build(array &$option){
        $my_attribute['title'] = '启用';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-success ajax-post confirm';
        $my_attribute['href']  = U(
            '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/resume'
        );

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }
}