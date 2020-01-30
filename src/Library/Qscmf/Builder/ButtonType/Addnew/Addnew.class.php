<?php
namespace Qscmf\Builder\ButtonType\Addnew;

use Qscmf\Builder\ButtonType\ButtonType;

class Addnew extends ButtonType{

    public function build(array $option){
        $my_attribute['title'] = '新增';
        $my_attribute['class'] = 'btn btn-primary';
        $my_attribute['href']  = U(MODULE_NAME.'/'.CONTROLLER_NAME.'/add');

        if ($option['attribute'] && is_array($option['attribute'])) {
            $option['attribute'] = array_merge($my_attribute, $option['attribute']);
        }

        return $this->compileButton($option);
    }
}