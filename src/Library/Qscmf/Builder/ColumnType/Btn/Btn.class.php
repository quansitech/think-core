<?php

namespace Qscmf\Builder\ColumnType\Btn;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\RuleType\Eq;
use AntdAdmin\Component\ColumnType\RuleType\Neq;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\ColumnType\Action;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableRightBtn;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\GenButton\TGenButton;


class Btn extends ColumnType implements IAntdTableColumn
{
    use TGenButton;

    public function build(array &$option, array $data, $listBuilder)
    {
        return $data[$option['name']];
    }

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        $col = new Action('', $options['title']);

        // 右侧操作
        if ($listBuilder->right_button_list) {
            $col->actions(function (Table\ColumnType\ActionsContainer $container) use ($listBuilder) {
                $this->handleRightBtn($container, $listBuilder);
            });
        }

        return $col;
    }

    protected function handleRightBtn(Table\ColumnType\ActionsContainer $container, $listBuilder)
    {
        $this->registerButtonType();

        foreach ($listBuilder->right_button_list as $item) {
            $class = $this->_button_type[$item['type']];
            $class = new $class();
            if (!($class instanceof IAntdTableRightBtn)) {
                E($item['type'] . ': 右侧操作未做处理');
            }

            $links = $class->tableRightBtnAntdRender($item, $listBuilder);
            !is_array($links) && $links = [$links];
            foreach ($links as $link) {
                $container->addOption($link);

                switch ($item['attribute']['{condition}'] ?? '') {
                    case '':
                        break;
                    case 'eq':
                        $link->addShowRules($item['attribute']['{key}'], [new Eq($item['attribute']['{value}'])]);
                        break;
                    case 'neq':
                        $link->addShowRules($item['attribute']['{key}'], [new Neq($item['attribute']['{value}'])]);
                        break;
                    default:
                        E($item['attribute']['{condition}'] . ': 暂不支持该条件');
                }

                if ($item['attribute']['href']) {
                    $url = str_replace('__data_id__', '__id__', $item['attribute']['href']);

                    if (Str::contains($item['attribute']['class'], ['ajax-get', 'ajax-post'])) {
                        $link->request(
                            Str::contains($item['attribute']['class'], 'ajax-get') ? 'get' : 'post',
                            $url,
                            [],
                            Str::contains($item['attribute']['class'], 'confirm') ? '确认操作？' : ''
                        );
                    } else {
                        $link->setHref($url);
                    }
                }
            }
        }
    }

}