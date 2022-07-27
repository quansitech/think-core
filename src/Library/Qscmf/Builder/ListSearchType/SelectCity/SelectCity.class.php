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

    static public function parse(string $key, string $map_key, array $get_data) : array{
        if(isset($get_data[$key]) && !qsEmpty($get_data[$key])){
            return  [$map_key => $get_data[$key]];
        }
        else{
            return [];
        }
    }
}