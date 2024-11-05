<?php
namespace Qscmf\Builder\ColumnType\Date;

use AntdAdmin\Component\ColumnType\BaseColumn;
use AntdAdmin\Component\ColumnType\Text;
use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableColumn;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Date extends ColumnType implements EditableInterface, IAntdTableColumn
{

    use \Qscmf\Builder\ButtonType\Save\TargetFormTrait;

    protected string $_template = __DIR__ . '/date.html';
    protected string $_default_format ='Y-m-d';

    public function build(array &$option, array $data, $listBuilder){

        return $this->formatDateVal($data[$option['name']], $option['value']);
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input date ". $this->getSaveTargetForm($listBuilder)." {$option['extra_class']}";

        $view = new \Think\View();
        $view->assign('gid', Str::uuid());
        $view->assign('options', $option);
        $view->assign('data', $data);
        $view->assign('class', $class);
        $view->assign('value', $this->formatDateVal($data[$option['name']], $option['value']));
        $view->assign('name', $this->buildName($option, $listBuilder));

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

    public function tableColumnAntdRender($options, &$datalist, $listBuilder): BaseColumn
    {
        foreach ($datalist as &$item) {
            $item[$options['name']] = $this->formatDateVal($item[$options['name']], $options['value']);
        }
        $col = new Text($options['name'], $options['title']);
        return $col;
    }
}