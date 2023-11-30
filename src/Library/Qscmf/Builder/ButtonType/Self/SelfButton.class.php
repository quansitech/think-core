<?php
namespace Qscmf\Builder\ButtonType\Self;

use Qscmf\Builder\ButtonType\ButtonType;
use Qscmf\Builder\ListBuilder;

class SelfButton extends ButtonType{

    public function build(array &$option, ListBuilder $listBuilder){
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-danger';

        $option['attribute'] = array_merge($my_attribute, is_array($option['attribute']) ? $option['attribute'] : [] );

        return '';
    }
}