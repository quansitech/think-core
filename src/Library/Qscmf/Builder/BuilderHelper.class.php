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
        $check_items = array_values(array_filter(array_map(function ($items){
            if ($items['auth_node']){
                $auth_node = (array)$items['auth_node'];
                $node = $auth_node['node'] ? (array)$auth_node['node'] : $auth_node;
                $logic = $auth_node['logic'] ? $auth_node['logic'] : 'and';

                switch ($logic){
                    case 'and':
                        foreach ($node as $v){
                            $has_auth = verifyAuthNode($v);
                            if (!$has_auth){
                                unset($items);
                                break;
                            }
                        }
                        break;
                    case 'or':
                        $false_count = 0;
                        foreach ($node as $v){
                            $has_auth = verifyAuthNode($v);
                            if ($has_auth){
                                break;
                            }else{
                                $false_count ++;
                            }
                        }
                        if ($false_count == count($node)){
                            unset($items);
                        }
                        break;
                    default:
                        E('Invalid logic value');
                        break;
                }
            }
            return $items;
        }, $check_items)));

        return $check_items;
    }

}