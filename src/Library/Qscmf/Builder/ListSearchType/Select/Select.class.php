<?php
namespace Qscmf\Builder\ListSearchType\Select;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class Select implements ListSearchType{

    public function build(array $item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/select.html');
        return $content;
    }
}