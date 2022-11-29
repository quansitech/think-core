<?php
namespace Qscmf\Builder\ListSearchType\Text;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class Text implements ListSearchType{

    public function build(array $item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/text.html');
        return $content;
    }

    static public function parse(string $key, string $map_key, array $get_data, string $rule = 'fuzzy') : array{
        if(isset($get_data[$key]) && !qsEmpty($get_data[$key])){
            return  $rule === 'exact' ? [$map_key => $get_data[$key]] : [$map_key => ['like', '%'. $get_data[$key] . '%']];
        }
        else{
            return [];
        }
    }
}