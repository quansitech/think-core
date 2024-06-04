<?php
namespace Qscmf\Builder\ButtonType\Addnew;

use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class Addnew extends ButtonType{

    public function build(array &$option, ListBuilder $listBuilder){
        $my_attribute['title'] = '新增';
        $my_attribute['class'] = 'btn btn-primary';
        $my_attribute['href']  = U(MODULE_NAME.'/'.CONTROLLER_NAME.'/add');

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }
}