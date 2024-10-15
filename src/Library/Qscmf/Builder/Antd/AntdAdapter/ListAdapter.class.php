<?php

namespace Qscmf\Builder\Antd\AntdAdapter;

use AntdAdmin\Component\ColumnType\RuleType\Eq;
use AntdAdmin\Component\ColumnType\RuleType\Neq;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Tabs;
use Illuminate\Support\Str;
use Qscmf\Builder\ColumnType\Fun\Fun;
use Qscmf\Builder\ColumnType\Time\Time;
use Qscmf\Builder\ListBuilder;
use Qscmf\Lib\Inertia\Inertia;

class ListAdapter
{
    protected ListBuilder $builder;

    public function __construct(ListBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function render()
    {
        if ($this->builder->nid) {
            Inertia::getInstance()->share('layoutProps.menuActiveKey', 'n-' . $this->builder->nid);
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
            $page = new Table\Pagination();
            $page->setPageSize($pagination['listRows']);
            $page->setCurrent(I('page', 1));
            $page->setTotal($pagination['totalRows']);
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
            return $table->render();
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

        return $tabs->render();
    }

    protected function handleColumns(Table\ColumnsContainer $container, mixed &$dataSource)
    {
        foreach ($this->builder->table_column_list as $column) {
            switch ($column['type']) {
                case 'btn':
                    break;
                case 'status':
                    $container->select($column['name'], $column['title'])
                        ->setOptions([
                            '0' => ['text' => '禁用', 'status' => 'Error'],
                            '1' => ['text' => '启用', 'status' => 'Success'],
                        ])
                        ->setSearch(false);
                    break;
                case 'time':
                    foreach ($dataSource as &$row) {
                        $fun = new Time();
                        $row[$column['name']] = $fun->build($column, $row, $this->builder);
                    }
                    $container->text($column['name'], $column['title'])->setSearch(false);
                    break;
                case 'fun':
                    foreach ($dataSource as &$row) {
                        $fun = new Fun();
                        $row[$column['name']] = $fun->build($column, $row, $this->builder);
                    }
                case '':
                case 'text':
                    $col = $container->text($column['name'], $column['title'])->setSearch(false);
                    if ($column['editable']) {
                        $col->editable();
                    }
                    break;
                default:
                    E($column['type'] . ': 列未做处理');
            }
        }

        // 搜索
        if ($search_list = $this->builder->search) {
            foreach ($search_list as $item) {
                switch ($item['type']) {
                    case 'text':
                        $container->text($item['name'], $item['title'])->hideInTable()->hideInForm();
                        break;
                    case 'date_range':
                        $container->dateRange($item['name'], $item['title'])->hideInTable()->hideInForm();
                        break;
                    case 'select':
                        $container->select($item['name'], $item['title'])
                            ->setOptions($item['options'])
                            ->hideInTable()
                            ->hideInForm();
                        break;
                    case 'select_text':
                        $container->select('key', $item['title'])
                            ->setOptions($item['options'])
                            ->hideInTable()
                            ->hideInForm();
                        $container->text('word', '')->hideInTable()->hideInForm();
                        break;
                    default:
                        E($item['type'] . ': 搜索项未做处理');
                }
            }
        }


        // 右侧操作
        if ($this->builder->right_button_list) {
            $container->option('', '操作')->options(function (Table\ColumnType\OptionsContainer $container) {
                $this->handleRightBtn($container);
            });
        }
    }

    protected function handleRightBtn(Table\ColumnType\OptionsContainer $container)
    {
        foreach ($this->builder->right_button_list as $item) {
            switch ($item['type']) {
                case 'edit':
                    $link = $container->link('编辑')->setHref(U('edit', ['id' => '__id__']));
                    break;
                case 'forbid':
                    $link = $container->link('禁用')
                        ->addShowRules('status', [new Eq(1)])
                        ->request('put', U('forbid'), ['ids' => '__id__']);
                    $container->link('启用')
                        ->addShowRules('status', [new Eq(0)])
                        ->request('put', U('resume'), ['ids' => '__id__']);
                    break;
                case 'delete':
                    $link = $container->link('删除')
                        ->setDanger(true)
                        ->request('delete', U('delete'), ['ids' => '__id__'], '确定删除？');
                    break;
                case 'self':
                    $link = $container->link($item['attribute']['title']);
                    break;
                default:
                    E($item['type'] . ': 右侧操作未做处理');
            }

            if ($item['attribute']['{condition}']) {
                switch ($item['attribute']['{condition}']) {
                    case 'eq':
                        $link->addShowRules($item['attribute']['{key}'], [new Eq($item['attribute']['{value}'])]);
                        break;
                    case 'neq':
                        $link->addShowRules($item['attribute']['{key}'], [new Neq($item['attribute']['{value}'])]);
                        break;
                    default:
                        E($item['attribute']['{condition}'] . ': 暂不支持该条件');
                }
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

    protected function handleTopButtonList(Table\ActionsContainer $container)
    {
        foreach ($this->builder->top_button_list as $button) {
            switch ($button['type']) {
                case 'addnew':
                    $container->button($button['attribute']['title'] ?? '新增')
                        ->link(U('add'));
                    break;
                case 'delete':
                    $container->button('删除')
                        ->relateSelection()
                        ->setProps([
                            'danger' => true,
                        ])
                        ->request('delete', U('delete'), ['ids' => '__id__'], '确定删除？');
                    break;
                case 'save':
                    $container->startEditable('编辑')
                        ->saveRequest('put', U('save'));
                    break;
                case 'forbid':
                    $container->button('禁用')
                        ->relateSelection()
                        ->request('put', U('forbid'), ['ids' => '__id__']);
                    break;
                case 'resume':
                    $container->button('启用')
                        ->relateSelection()
                        ->request('put', U('resume'), ['ids' => '__id__']);
                    break;
                case 'self':
                    $btn = $container->button($button['attribute']['title']);
                    if ($button['attribute']['href']) {
                        $btn->link($button['attribute']['href']);
                    }
                    break;
                default:
                    E($button['type'] . ': 顶部按钮未做处理');
            }
        }
    }
}