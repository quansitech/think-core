<?php
namespace Qscmf\Builder\ButtonType;

abstract class ButtonType{

    public abstract function build(array &$option);

    protected function compileButton($option){
        if($option['tips'] != ''){
            $tips_html = '<span class="badge">' . $option['tips'] . '</span>';
        }

         return <<<HTML
<a {$this->compileHtmlAttr($option['attribute'])}>{$option['attribute']['title']} {$tips_html}</a>
HTML;
    }

    //编译HTML属性
    protected function compileHtmlAttr($attr) {
        $result = array();
        foreach ($attr as $key => $value) {

            if(!empty($value) && !is_array($value)){
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
        }
        $result = implode(' ', $result);
        return $result;
    }
}