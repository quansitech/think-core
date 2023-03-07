<?php
namespace Qscmf\Builder\ListSearchType\Self;

use Qscmf\Builder\ListSearchType\ListSearchType;

class Self_ implements ListSearchType{

    public function build(array $item){
        $templateFile = $item['options']['templateFile']?:'';
        $content= $item['options']['content']?:'';

        $view = new View();
        $view->assign('item', $item);
        return empty($templateFile)? $content : $view->fetch($templateFile,$content);
    }
}
