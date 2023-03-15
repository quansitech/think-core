<?php
namespace Qscmf\Builder\ListSearchType\Self;

use Qscmf\Builder\ListSearchType\ListSearchType;

class Self_ implements ListSearchType{

    public function build(array $item){
        return $item['options']['value']?:'';
    }
}