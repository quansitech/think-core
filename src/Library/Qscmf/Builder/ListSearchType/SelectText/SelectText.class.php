<?php
namespace Qscmf\Builder\ListSearchType\SelectText;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class SelectText implements ListSearchType{

    public function build($item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/select_text.html');
        return $content;
    }
}