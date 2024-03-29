<?php
namespace Qscmf\Builder\ListRightButton\Self;

use Qscmf\Builder\ListRightButton\ListRightButton;

class SelfButton extends ListRightButton{

    public function build(array &$option, array $data, $listBuilder){
        $my_attribute['class'] = 'default';

        $option['attribute'] = $listBuilder->mergeAttr($my_attribute, is_array($option['attribute']) ? $option['attribute'] : []);
        return '';
    }

}