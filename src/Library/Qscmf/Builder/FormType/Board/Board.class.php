<?php
namespace Qscmf\Builder\FormType\Board;

use Illuminate\Support\Str;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Board implements FormType {

    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $view->assign('gid', Str::uuid());
        $content = $view->fetch(__DIR__ . '/board.html');
        return $content;
    }
}