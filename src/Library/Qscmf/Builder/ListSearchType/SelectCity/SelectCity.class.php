<?php
namespace Qscmf\Builder\ListSearchType\SelectCity;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class SelectCity implements ListSearchType{

    public function build(array $item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/select_city.html');
        return $content;
    }
}