<?php
namespace Qscmf\Builder\ListSearchType\Hidden;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class Hidden implements ListSearchType{

    public function build(array $item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/hidden.html');
        return $content;
    }
}