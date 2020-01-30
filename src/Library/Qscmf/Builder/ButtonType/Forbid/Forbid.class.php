<?php
namespace Qscmf\Builder\ButtonType\Forbid;

use Qscmf\Builder\ButtonType\ButtonType;

class Forbid extends ButtonType{

    public function build(array $option){
        $my_attribute['title'] = '禁用';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-warning ajax-post confirm';
        $my_attribute['href']  = U(
            '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/forbid'
        );

        if ($option['attribute'] && is_array($option['attribute'])) {
            $option['attribute'] = array_merge($my_attribute, $option['attribute']);
        }

        return $this->compileButton($option);
    }
}