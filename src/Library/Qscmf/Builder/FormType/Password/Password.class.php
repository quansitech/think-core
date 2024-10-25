<?php
namespace Qscmf\Builder\FormType\Password;

use AntdAdmin\Component\ColumnType\BaseColumn;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter\IAntdFormColumn;
use Qscmf\Builder\FormType\FormType;
use Think\View;

class Password implements FormType, IAntdFormColumn
{

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/password.html');
        return $content;
    }

    public function formColumnAntdRender($options): BaseColumn
    {
        $column = new \AntdAdmin\Component\ColumnType\Password($options['name'], $options['title']);

        return $column;
    }
}