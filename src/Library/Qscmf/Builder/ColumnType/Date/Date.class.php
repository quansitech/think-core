<?php
namespace Qscmf\Builder\ColumnType\Date;

use Illuminate\Support\Str;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Date extends ColumnType implements EditableInterface{

    use \Qscmf\Builder\ButtonType\Save\TargetFormTrait;

    protected  $_template = __DIR__ . '/date.html';
    protected  $_default_format ='Y-m-d';

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

        return $view->fetch($this->_template);
    }

    protected function formatDateVal($value, $format = null){
        $format = $format?:$this->_default_format;
        return qsEmpty($value) ? '' : time_format($value, $format);
    }

    static public function registerCssAndJs():?array {
        return null;
    }

    static public function registerEditCssAndJs():?array {
        return [
            "<script src='".asset('libs/cui/cui.extend.min.js')."' ></script>",
            "<script src='".asset('libs/bootstrap-datepicker/bootstrap-datepicker.js')."' ></script>",
            "<script src='".asset('libs/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js')."' ></script>",
            "<script src='".asset('libs/bootstrap-datetimepicker/locales/bootstrap-datetimepicker.zh-CN.js')."' ></script>",
        ];
    }

}