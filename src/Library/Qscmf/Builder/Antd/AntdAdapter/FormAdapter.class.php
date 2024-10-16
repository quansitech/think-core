<?php

namespace Qscmf\Builder\Antd\AntdAdapter;

use AntdAdmin\Component\Form;
use AntdAdmin\Component\Tabs;
use Qscmf\Builder\FormBuilder;
use Qscmf\Lib\Inertia\Inertia;

class FormAdapter
{
    protected FormBuilder $builder;

    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function render()
    {
        $form = new Form();
        if ($this->builder->nid) {
            Inertia::getInstance()->share('layoutProps.menuActiveKey', 'n-' . $this->builder->nid);
        }
        if ($this->builder->meta_title) {
            Inertia::getInstance()->share('layoutProps.metaTitle', $this->builder->meta_title);
        }
        $form->setInitialValues($this->builder->form_data);
        $form->columns(function (Form\ColumnsContainer $container) {
            $this->handleFormItem($container);
        });

        $form->setSubmitRequest('post', $this->builder->post_url, null, [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);

        $form->actions(function (Form\ActionsContainer $container) {
            $this->handleAction($container);
        });
        if (!$this->builder->tab_nav) {
            return $form->render();
        }

        // 有tab页
        $tabs = new Tabs();
        $tab_nav = $this->builder->tab_nav;
        foreach ($tab_nav['tab_list'] as $index => $tab) {
            if ($index == $tab_nav['current_tab']) {
                $form->setSearchUrl($tab['href']);
                $tabs->addTab('t-' . $index, $tab['title'], $form, $tab['href']);
            } else {
                $tabs->addTab('t-' . $index, $tab['title'], null, $tab['href']);
            }
        }
        $tabs->setDefaultActiveKey('t-' . $tab_nav['current_tab']);
        return $tabs->render();
    }

    protected function handleFormItem(Form\ColumnsContainer $container)
    {
        foreach ($this->builder->form_items as $form_item) {
            switch ($form_item['type']) {
                case 'text':
                case 'num':
                    $item = $container->text($form_item['name'], $form_item['title']);
                    break;
                case 'textarea':
                case 'array':
                    $item = $container->textarea($form_item['name'], $form_item['title']);
                    break;
                case 'picture':
                    $item = $container->image($form_item['name'], $form_item['title']);
                    break;
                case 'select':
                    $item = $container->select($form_item['name'], $form_item['title'])
                        ->setValueEnum($form_item['options']);
                    break;
                case 'ueditor':
                    $item = $container->ueditor($form_item['name'], $form_item['title']);
                    break;
                case 'password':
                    $item = $container->password($form_item['name'], $form_item['title']);
                    break;
                case 'hidden':
                    $item = $container->text($form_item['name'], $form_item['title'])->hideInForm();
                    break;
                default:
                    E($form_item['type'] . ': 表单项未做处理');
            }
            if ($form_item['tip']) {
                $item->setTips($form_item['tip']);
            }
        }
    }

    protected function handleAction(Form\ActionsContainer $container)
    {
        $container->button($this->builder->submit_btn_title)
            ->setProps([
                'type' => 'primary'
            ])
            ->submit();

        $container->button('返回')->back();
    }

}