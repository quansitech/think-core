<?php
namespace Qscmf\Builder\ListSearchType\Select;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableSearch;
use Qscmf\Builder\ListSearchType\ListSearchType;
use Think\View;

class Select implements ListSearchType, IAntdTableSearch
{

    public function build(array $item){
        $options = $item['options'] instanceof SelectBuilder ? $item['options'] :
        $this->buildDefBuilder((array)$item['options']);

        !$options->getPlaceholder() && $options->setPlaceholder($item['title']);

        $view = new View();
        $view->assign('item', $item);
        $view->assign('select_opt', $options->toArray());
        $view->assign('value', I('get.'.$item['name']));
        $view->assign('show_placeholder', $options->show_placeholder);
        $content = $view->fetch(__DIR__ . '/select.html');
        return $content;
    }

    protected function buildDefBuilder(array $options):SelectBuilder{
        return new SelectBuilder($options);
    }

    static public function parse(string $key, string $map_key, array $get_data) : array{
        if(isset($get_data[$key]) && !qsEmpty($get_data[$key])){
            return  [$map_key => $get_data[$key]];
        }
        else{
            return [];
        }
    }

    public function tableSearchAntdRender($options, $listBuilder): BaseColumn
    {
        $col = new \AntdAdmin\Component\ColumnType\Select($options['name'], $options['title']);
        $col->setValueEnum($options['options']);
        return $col;
    }
}