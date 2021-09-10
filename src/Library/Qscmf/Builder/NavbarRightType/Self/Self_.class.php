<?php
namespace Qscmf\Builder\NavbarRightType\Self;

use Qscmf\Builder\NavbarRightType\NavbarRightType;

class Self_ extends NavbarRightType {

    public function build(array &$option){
        return $option['options'];
    }
}