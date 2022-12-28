<?php

namespace Qscmf\Builder\ColumnType\Select2;

use Illuminate\Support\Str;
use Qscmf\Builder\ButtonType\Save\TargetFormTrait;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\EditableInterface;

class Select2 extends ColumnType implements EditableInterface{

    use TargetFormTrait;

    public function build(array &$option, array $data, $listBuilder){
        $this->extraValue($option);
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

    protected function extraValue(&$option){
        if(isset($option['value']['options'])){
            $option['select2_options'] = $option['value'];
            $this->handleSelectOptions($option['select2_options']);

            $option['value'] = $option['value']['options'];
        }
    }

    protected function handleSelectOptions(&$select_options){
        if (!empty($select_options)){
            unset($select_options['options']);

            $select_options['tags'] = match ($select_options['tags']){
                true,'true' => "true",
                default => "false"
            };

            $select_options['allow_clear'] = match ($select_options['allow_clear']){
                false,'false' => 'false',
                default => 'true'
            };
        }
    }

    public function editBuild(&$option, $data, $listBuilder){
        $this->extraValue($option);
        $class = "form-control input ". $this->getSaveTargetForm(). " {$option['extra_class']}";

        $view = new \Think\View();
        $view->assign('gid', Str::uuid());
        $view->assign('options', $option);
        $view->assign('data', $data);
        $view->assign('class', $class);
        $view->assign('name', $option['name']);
        $view->assign('value', $data[$option['name']]);
        $view->assign('select2_options', $option['select2_options']);
        return $view->fetch(__DIR__ . '/select2.html');
    }

    static public function registerCssAndJs():?array {
        return null;
    }

    static public function registerEditCssAndJs():?array {
        return self::getCssAndJs();
    }

    static protected function getCssAndJs() :array {
        return [
            "<link rel='stylesheet' href='".asset('libs/select2/css/select2.min.css')."' />",
            "<script src='".asset('libs/select2/js/select2.full.min.js')."' ></script>",
        ];
    }
}