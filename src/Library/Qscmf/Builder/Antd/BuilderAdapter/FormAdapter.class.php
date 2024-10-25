<?php

namespace Qscmf\Builder\Antd\BuilderAdapter;

use AntdAdmin\Component\Form;
use AntdAdmin\Component\Tabs;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormButton;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormBuilder;
use Qscmf\Builder\FormType\FormTypeRegister;
use Qscmf\Builder\GenButton\TGenButton;
use Qscmf\Lib\Inertia\Inertia;

class FormAdapter
{
    use FormTypeRegister, TGenButton;

    protected FormBuilder $builder;

    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getForm(): Form|Tabs
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
            return $form;
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
        return $tabs;
    }

    public function render()
    {
        return $this->getForm()->render();
    }

    protected function handleFormItem(Form\ColumnsContainer $container)
    {
        $this->registerFormType();

        foreach ($this->builder->form_items as $form_item) {
            $class = $this->_form_type[$form_item['type']];
            $class = new $class;
            if (!$class instanceof IAntdFormColumn) {
                E($form_item['type'] . '表单项未做处理');
            }
            $fi = $class->formColumnAntdRender($form_item);
            $container->addColumn($fi);

            if ($form_item['tip']) {
                $fi->setTips($form_item['tip']);
            }
        }
    }

    protected function handleAction(Form\ActionsContainer $container)
    {
        $this->registerButtonType();

        $container->button($this->builder->submit_btn_title)
            ->setProps([
                'type' => 'primary'
            ])
            ->submit();

        if ($this->builder->button_list) {
            foreach ($this->builder->button_list as $item) {
                $class = $this->_button_type[$item['type']];
                $class = new $class;
                if (!$class instanceof IAntdFormButton) {
                    E($item['type'] . '按钮未做处理');
                }

                $btn = $class->formButtonAntdRender($item);
                $container->addAction($btn);
            }
        }

        $container->button('返回')->back();
    }

}