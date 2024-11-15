<?php

namespace Qscmf\Builder\Antd\BuilderAdapter;

use AntdAdmin\Component\Table;
use AntdAdmin\Component\Tabs;
use Bootstrap\RegisterContainer;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableButton;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableSearch;
use Qscmf\Builder\ColumnType\Text\Text;
use Qscmf\Builder\GenColumn\TGenColumn;
use Qscmf\Builder\ListBuilder;
use Qscmf\Lib\Inertia\Inertia;

class ListAdapter
{
    use TGenColumn;

    protected ListBuilder $builder;
    protected array $search_type_map;
    protected $top_button_type_map;

    public function __construct(
        ListBuilder $builder,
                    $search_type_map,
                    $top_button_type_map,
    )
    {
        $this->builder = $builder;
        $this->search_type_map = $search_type_map;
        $this->top_button_type_map = $top_button_type_map;
    }

    public function getTable(): Table|Tabs
    {
        if ($this->builder->nid) {
            Inertia::share('layoutProps.menuActiveKey', 'n-' . $this->builder->nid);
        }

        $this->builder->assignBuildData();
        $dataSource = $this->builder->table_data_list;
        $table = new Table();
        $table->setMetaTitle($this->builder->meta_title)
            ->setDateFormatter('YYYY/MM/DD')
            ->setRowSelection(true)
            ->columns(function (Table\ColumnsContainer $container) use (&$dataSource) {
                $this->handleColumns($container, $dataSource);
            })
            ->setDataSource($dataSource);

        // 分页
        $pagination = $this->builder->pagination;
        if ($pagination['show'] ?? false) {
            $page = new Table\Pagination(I(C('VAR_PAGE'), 1), $pagination['listRows'], $pagination['totalRows'], C('VAR_PAGE'));
            $table->setPagination($page);
        }

        // 按钮
        if ($this->builder->top_button_list) {
            $table->actions(function (Table\ActionsContainer $container) {
                $this->handleTopButtonList($container);
            });
        }

        // 搜索
        if (!$this->builder->search) {
            $table->setSearch(false);
        }

        $headers = getallheaders();
        if (!$this->builder->tab_nav || $headers['X-Table-Search']) {
            //判断 table搜索 直接返回数据
            return $table;
        }

        // 有tab页
        $tabs = new Tabs();
        $tab_nav = $this->builder->tab_nav;
        foreach ($tab_nav['tab_list'] as $index => $tab) {
            if ($index == $tab_nav['current_tab']) {
                $table->setSearchUrl($tab['href']);
                $tabs->addTab('t-' . $index, $tab['title'], $table, $tab['href']);
            } else {
                $tabs->addTab('t-' . $index, $tab['title'], null, $tab['href']);
            }
        }
        $tabs->setDefaultActiveKey('t-' . $tab_nav['current_tab']);

        return $tabs;
    }

    public function render()
    {
        return $this->getTable()->render();
    }

    protected function handleColumns(Table\ColumnsContainer $container, mixed &$dataSource)
    {
        $column_type_map = array_merge($this->registerBaseColumnType(), RegisterContainer::getListColumnType());

        foreach ($this->builder->table_column_list as $column) {
            if ($column_type_map[$column['type']]) {
                $class = new $column_type_map[$column['type']];
            } else {
                $class = new Text();
            }
            if ($class instanceof IAntdTableColumn) {
                $col = $class->tableColumnAntdRender($column, $dataSource, $this->builder);
                $container->addColumn($col);

                $column['editable'] && $col->editable();
                $col->setSearch(false);
            } else {
                E($column['type'] . ': 列未做处理');
            }
        }

        // 搜索
        if ($search_list = $this->builder->search) {
            foreach ($search_list as $item) {
                $class = $this->search_type_map[$item['type']];
                $class = new $class;
                if ($class instanceof IAntdTableSearch) {
                    $col = $class->tableSearchAntdRender($item, $this->builder);
                    !is_array($col) && $col = [$col];
                    foreach ($col as $c) {
                        $container->addColumn($c);

                        $c->hideInTable()->hideInForm();
                    }
                } else {
                    E($item['type'] . ': 搜索项未做处理');
                }
            }
        }
    }

    protected function handleTopButtonList(Table\ActionsContainer $container)
    {
        foreach ($this->builder->top_button_list as $button) {
            $class = $this->top_button_type_map[$button['type']];
            $class = new $class;
            if ($class instanceof IAntdTableButton) {
                $btn = $class->tableButtonAntdRender($button, $this->builder);
                !is_array($btn) && $btn = [$btn];
                foreach ($btn as $item) {
                    $container->addAction($item);
                }
            } else {
                E($button['type'] . ': 按钮未做处理');
            }
        }
    }
}