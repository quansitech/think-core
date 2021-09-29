<?php

namespace Qscmf\Builder;

class BuilderHelper
{
    static public function compileHtmlAttr($attr) {
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

    /**
     * 检测字段的权限点，无权限则unset该item
     *
     * @param array $check_items
     * @return array
     */
    static public function checkAuthNode($check_items){
        return filterItemsByAuthNode($check_items);
    }

}