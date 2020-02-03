<?php
namespace Qscmf\Builder\ListSearchType\Text;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class Text implements ListSearchType{

    public function build($item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/text.html');
        return $content;
    }
}