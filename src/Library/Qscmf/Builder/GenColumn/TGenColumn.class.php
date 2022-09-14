<?php

namespace Qscmf\Builder\GenColumn;

use Bootstrap\RegisterContainer;
use Qscmf\Builder\ButtonType\Save\DefaultEditableColumn;
use Qscmf\Builder\ColumnType\A\A;
use Qscmf\Builder\ColumnType\Btn\Btn;
use Qscmf\Builder\ColumnType\Date\Date;
use Qscmf\Builder\ColumnType\EditableInterface;
use Qscmf\Builder\ColumnType\Fun\Fun;
use Qscmf\Builder\ColumnType\Hidden\Hidden;
use Qscmf\Builder\ColumnType\Icon\Icon;
use Qscmf\Builder\ColumnType\Num\Num;
use Qscmf\Builder\ColumnType\Picture\Picture;
use Qscmf\Builder\ColumnType\Select\Select;
use Qscmf\Builder\ColumnType\Select2\Select2;
use Qscmf\Builder\ColumnType\Self\Self_;
use Qscmf\Builder\ColumnType\Status\Status;
use Qscmf\Builder\ColumnType\Time\Time;
use Qscmf\Builder\ColumnType\Type\Type;
use Qscmf\Builder\ColumnType\Textarea\Textarea;
use Qscmf\Builder\ColumnType\Checkbox\Checkbox;
use Qscmf\Builder\ColumnType\Pictures\Pictures;

trait TGenColumn
{
    private $_column_type = [];
    private $_default_column_type = \Qscmf\Builder\ColumnType\Text\Text::class;
    private $_table_data_list_key = 'id';  // 表格数据列表主键字段名
    private $_column_css_and_js = [];

    public function registerColumnType(){
        static $column_type = [];
        if(empty($column_type)){
            $base_column_type = self::registerBaseColumnType();
            $column_type = array_merge($base_column_type, RegisterContainer::getListColumnType());
        }
        $this->_column_type = $column_type;
    }

    protected function registerBaseColumnType(){
        return [
            'status' => Status::class,
            'icon' => Icon::class,
            'date' => Date::class,
            'time' => Time::class,
            'picture' => Picture::class,
            'pictures' => Pictures::class,
            'type' => Type::class,
            'fun' => Fun::class,
            'a' => A::class,
            'self' => Self_::class,
            'num' => Num::class,
            'btn' => Btn::class,
            'textarea' => Textarea::class,
            'checkbox' => Checkbox::class,
            'hidden' => Hidden::class,
            'select' => Select::class,
            'select2' => Select2::class,
        ];
    }

    public function genOneColumnOpt($name, $title, $type = null, $value = '', $editable = false, $tip = '',
                                    $th_extra_attr = '', $td_extra_attr = '', $auth_node = '', $extra_attr = '', $extra_class = '') {

        return compact('name','title','editable','type','value','tip','th_extra_attr',
            'td_extra_attr','auth_node','extra_class','extra_attr');
    }

    public function buildOneColumnItem(&$column, &$data){
        $is_editable = $this->isEditable($column, $data);
        if($is_editable && !isset($data[$this->_hidden_key])){
            $hidden = new Hidden();
            $hidden_column = [
                'name' => $this->_table_data_list_key
            ];
            $data[$this->_hidden_key] = $hidden->editBuild($hidden_column, $data, $this);
        }


        $column_type = $this->_column_type[$column['type']] ?? $this->_default_column_type;
        $column_type_class = new $column_type();


        if ($column_type_class){
            $column_content = $is_editable && $column_type_class instanceof EditableInterface ?
                $column_type_class->editBuild($column, $data, $this) :
                $column_type_class->build($column, $data, $this);
            $column_content = $this->parseData($column_content, $data);
            $is_editable ? $this->getEditCssAndJs($column_type_class) : $this->getReadonlyCssAndJs($column_type_class);
        }

        if ($is_editable && !$column_type_class instanceof EditableInterface){
            $column_content = (new DefaultEditableColumn())->build($column, $data, $this);
        }

        if($column === 'right_button'){
            $data[$column['name']] = "<td nowrap {$column['td_extra_attr']}>{$column_content}</td>";
        }
        else{
            $data[$column['name']] = "<td {$column['td_extra_attr']}>{$column_content}</td>";
        }

    }

    protected function isEditable($column, $data) : bool{
        if($column['editable'] && $column['editable'] instanceof \Closure){
            return $column['editable']($data);
        }
        else{
            return $column['editable'];
        }
    }

    protected function parseData($str, $data){
        while(preg_match('/__(\w+?)__/i', $str, $matches)){
            $str = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $str);
        }
        return $str;
    }

    public function getTableDataListKey(){
        return $this->_table_data_list_key;
    }

    protected function getReadonlyCssAndJs($column_cls){
        $this->getCssAndJs($column_cls, 'registerCssAndJs');
    }

    protected function getCssAndJs($column_cls, $method_name){
        if (method_exists($column_cls, $method_name)){
            $source = $column_cls::$method_name();
            is_array($source) && $this->_column_css_and_js = array_merge($this->_column_css_and_js,$source);
        }
    }

    protected function getEditCssAndJs($column_cls){
        $this->getCssAndJs($column_cls, 'registerEditCssAndJs');
    }

    public function getUniqueColumnCssAndJs():string{
        return implode(PHP_EOL,array_unique($this->_column_css_and_js));
    }
}