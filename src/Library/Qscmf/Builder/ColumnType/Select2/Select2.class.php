<?php

namespace Qscmf\Builder\ColumnType\Select2;

use Illuminate\Support\Str;
use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Select2 extends ColumnType implements EditableInterface{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        $text = $this->getSelectText($option['value'], $data[$option['name']]);
        return '<span title="' .$text. '" >' . $text . '</span>';
    }

    protected function getSelectText($options, $value){
        $selected_text = '';
        if(!qsEmpty($value)){
            $value = is_array($value) ? $value : explode(',', $value);
        }
        if(!qsEmpty($value)){
            $children_option = array_column($options, 'children');
            if (!empty($children_option)){
                $new_options = [];
                array_map(function($option) use(&$new_options){
                    $new_options = array_merge($new_options, $option);
                }, $children_option);
                $options = array_column($new_options, 'text', 'id');
            }
            $selected_value = array_filter($options,function($text, $key) use($value){
                return in_array($key, $value);
            }, ARRAY_FILTER_USE_BOTH);

            $selected_text = implode(',',$selected_value);
        }

        return $selected_text;
    }

    public function editBuild(&$option, $data, $listBuilder){
        $class = "form-control input ". $this->getSaveTargetForm(). " {$option['extra_class']}";

        $view = new \Think\View();
        $view->assign('gid', Str::uuid());
        $view->assign('options', $option);
        $view->assign('data', $data);
        $view->assign('class', $class);
        $view->assign('name', $option['name']);
        $view->assign('value', $data[$option['name']]);
        return $view->fetch(__DIR__ . '/select2.html');
    }

    static public function registerCssAndJs():array {
        return '';
    }

    static public function registerEditCssAndJs():array {
        return self::getCssAndJs();
    }

    static protected function getCssAndJs() :array {
        $css_href = __ROOT__."/Public/libs/select2/css/select2.min.css";
        $js_src = __ROOT__."/Public/libs/select2/js/select2.full.min.js";
        return [
            <<<str
<link rel='stylesheet' href="$css_href">
str,
        <<<str
<script type="text/javascript" src="$js_src"></script>
str
        ];
    }
}