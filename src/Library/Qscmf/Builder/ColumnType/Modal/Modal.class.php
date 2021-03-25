<?php

namespace Qscmf\Builder\ColumnType\Modal;


use Illuminate\Support\Str;
use Qscmf\Builder\ColumnType\ColumnType;
use Think\View;

class Modal extends ColumnType
{
    public function build(array &$option, array $data, $listBuilder)
    {
        $view = new View();
        $view->assign('gid', Str::uuid());
        $view->assign('item', $option);
        $view->assign('value', $data[$option['name']]);

        return $view->fetch(__DIR__.'/modal.html');
    }
}