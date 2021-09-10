<?php
namespace Qscmf\Builder\NavbarRightType\Num;

use Qscmf\Builder\BuilderHelper;
use Qscmf\Builder\NavbarRightType\NavbarRightType;

class Num extends NavbarRightType {

    public function build(array &$option){
        return '<a ' . BuilderHelper::compileHtmlAttr($option['attribute']) . ' >' . $option['attribute']['title'] .
            '<span class="number">'.$option['options'].'</span></a>';
    }

}