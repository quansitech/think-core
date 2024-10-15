<?php

namespace Qscmf\Builder\Antd\AntdAdapter;

use AntdAdmin\Component\Form;
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

        $form->actions(function (Form\ActionsContainer $container) {
            $this->handleAction($container);
        });
        return $form->render();
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
                    $item = $container->textarea($form_item['name'], $form_item['title']);
                    break;
                case 'picture':
                    $item = $container->image($form_item['name'], $form_item['title']);
                    break;
                case 'select':
                    $item = $container->select($form_item['name'], $form_item['title'])
                        ->setOptions($form_item['options']);
                    break;
                case 'ueditor':
                    $item = $container->ueditor($form_item['name'], $form_item['title']);
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
            ->submit('post', $this->builder->post_url);
    }

}