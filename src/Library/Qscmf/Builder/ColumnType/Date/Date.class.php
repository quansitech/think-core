<?php
namespace Qscmf\Builder\ColumnType\Date;

use Illuminate\Support\Str;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Date extends ColumnType implements EditableInterface{

    use \Qscmf\Builder\ButtonType\Save\TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){

        return $this->formatDateVal($data[$option['name']], $option['value']);
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input date ". $this->getSaveTargetForm()." {$option['extra_class']}";

        $view = new \Think\View();
        $view->assign('gid', Str::uuid());
        $view->assign('options', $option);
        $view->assign('data', $data);
        $view->assign('class', $class);
        $view->assign('value', $this->formatDateVal($data[$option['name']], $option['value']));

        return $view->fetch(__DIR__ . '/date.html');
    }

    protected function formatDateVal($value, $format = null){
        $format = $format?:'Y-m-d';
        return qsEmpty($value) ? '' : time_format($value, $format);
    }
}