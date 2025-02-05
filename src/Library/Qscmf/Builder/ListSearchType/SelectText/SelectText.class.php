<?php

namespace Qscmf\Builder\ListSearchType\SelectText;

use AntdAdmin\Component\ColumnType\Select;
use AntdAdmin\Component\ColumnType\Text;
use Qscmf\Builder\ListSearchType\ListSearchType;
use Quansitech\BuilderAdapterForAntdAdmin\BuilderAdapter\ListAdapter\IAntdTableSearch;
use Think\View;

class SelectText implements ListSearchType, IAntdTableSearch
{

    public function build(array $item)
    {
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/select_text.html');
        return $content;
    }

    static public function parse(array $keys_rule, array $get_data): array
    {
        if (isset($get_data['key']) && $get_data['word']) {

            return match (true) {
                $keys_rule[$get_data['key']]['rule'] === 'fuzzy' => [$keys_rule[$get_data['key']]['map_key'] => ['like', "%{$get_data['word']}%"]],
                $keys_rule[$get_data['key']]['rule'] === 'exact' => [$keys_rule[$get_data['key']]['map_key'] => $get_data['word']],
                $keys_rule[$get_data['key']]['rule'] instanceof \Closure => $keys_rule[$get_data['key']]['rule']($keys_rule[$get_data['key']]['map_key'], $get_data['word']),
                default => E("undefined rule"),
            };
        } else {
            return [];
        }
    }

    public function tableSearchAntdRender($options, $listBuilder): array
    {
        $key = new Select('key', $options['title']);
        $key->setValueEnum($options['options']);

        $word = new Text('word', '');
        return [$key, $word];
    }
}