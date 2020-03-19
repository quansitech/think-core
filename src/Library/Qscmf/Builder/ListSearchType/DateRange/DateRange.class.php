<?php
namespace Qscmf\Builder\ListSearchType\DateRange;

use Qscmf\Builder\ListSearchType\ListSearchType;
use Think\View;

class DateRange implements ListSearchType{

    public function build($item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/date_range.html');
        return $content;
    }
}