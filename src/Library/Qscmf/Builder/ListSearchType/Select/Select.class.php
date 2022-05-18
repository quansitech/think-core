<?php
namespace Qscmf\Builder\ListSearchType\Select;

use Think\View;
use Qscmf\Builder\ListSearchType\ListSearchType;

class Select implements ListSearchType{

    public function build(array $item){
        $options = $item['options'] instanceof SelectBuilder ? $item['options'] :
        $this->buildDefBuilder((array)$item['options']);

        !$options->getPlaceholder() && $options->setPlaceholder($item['title']);

        $view = new View();
        $view->assign('item', $item);
        $view->assign('select_opt', $options->toArray());
        $view->assign('value', I('get.'.$item['name']));
        $content = $view->fetch(__DIR__ . '/select.html');
        return $content;
    }

    protected function buildDefBuilder(array $options):SelectBuilder{
        return new SelectBuilder($options);
    }

}