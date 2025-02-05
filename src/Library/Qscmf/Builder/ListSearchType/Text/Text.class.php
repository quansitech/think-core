<?php
namespace Qscmf\Builder\ListSearchType\Text;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\ListSearchType\ListSearchType;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableSearch;
use Think\View;

class Text implements ListSearchType, IAntdTableSearch
{

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

    public function tableSearchAntdRender($options, $listBuilder): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Text($options['name'], $options['title']);
        return $column;
    }
}