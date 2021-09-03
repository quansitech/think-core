<?php

namespace Qscmf\Builder\GenButton;

use Bootstrap\RegisterContainer;

trait TGenButton
{
    private $_button_type;
    private $_button_dom_type = 'a';

    public function registerButtonType(){
        static $button_type = [];
        if(empty($button_type)) {
            $base_button_type = self::registerBaseButtonType();
            $button_type = array_merge($base_button_type, RegisterContainer::getListRightButtonType());
        }

        $this->_button_type = $button_type;
    }

    protected function registerBaseButtonType(){

        return [
            'forbid' => \Qscmf\Builder\ListRightButton\Forbid\Forbid::class,
            'edit' => \Qscmf\Builder\ListRightButton\Edit\Edit::class,
            'delete' => \Qscmf\Builder\ListRightButton\Delete\Delete::class,
            'self' => \Qscmf\Builder\ListRightButton\Self\SelfButton::class
        ];
    }

    public function parseButtonList($button_list, &$data){
        $new_button_list = [];
        foreach ($button_list as $one_button) {

            if(isset($one_button['attribute']['{key}']) && isset($one_button['attribute']['{condition}']) && isset($one_button['attribute']['{value}'])){
                $continue_flag = false;
                switch($one_button['attribute']['{condition}']){
                    case 'eq':
                        if($data[$one_button['attribute']['{key}']] != $one_button['attribute']['{value}']){
                            $continue_flag = true;
                        }
                        break;
                    case 'neq':
                        if($data[$one_button['attribute']['{key}']] == $one_button['attribute']['{value}']){
                            $continue_flag = true;
                        }
                        break;
                }
                if($continue_flag){
                    continue;
                }
                unset($one_button['attribute']['{key}']);
                unset($one_button['attribute']['{condition}']);
                unset($one_button['attribute']['{value}']);
            }

            if($one_button['options']){
                $json_options = json_encode($one_button['options']);
                $json_options = $this->parseData($json_options, $data);
                $one_button['options'] = json_decode($json_options, true);
            }

            if(isset($one_button['attribute']['title']) && empty($one_button['attribute']['title'])){
                unset($one_button['attribute']['title']);
            }
            $content = (new $this->_button_type[$one_button['type']]())->build($one_button, $data, $this);
            $button_html = self::compileRightButton($one_button, $data);
            $tmp = <<<HTML
{$button_html}&nbsp;
{$content}
HTML;
            $new_button_list[] = $tmp;

        }

        return $new_button_list;
    }


    protected function parseData($str, $data){
        while(preg_match('/__(\w+?)__/i', $str, $matches)){
            $str = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $str);
        }
        return $str;
    }

    //编译HTML属性
    protected function compileHtmlAttr($attr) {
        $result = array();
        foreach ($attr as $key => $value) {
            if($key == 'tips'){
                continue;
            }

            if(!empty($value) && !is_array($value)){
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
        }
        $result = implode(' ', $result);
        return $result;
    }

    protected function compileRightButton($option, $data){
        // 将约定的标记__data_id__替换成真实的数据ID
        $option['attribute']['href'] = preg_replace(
            '/__data_id__/i',
            $data[$this->getPrimaryKey()],
            $option['attribute']['href']
        );

        //将data-id的值替换成真实数据ID
        $option['attribute']['data-id'] = preg_replace(
            '/__data_id__/i',
            $data[$this->getPrimaryKey()],
            $option['attribute']['data-id']
        );

        $tips = '';
        if($option['tips'] && is_string($option['tips'])){
            $tips = ' <span class="badge">' . $option['tips'] . '</span>';
        }
        else if($option['tips'] && $option['tips'] instanceof \Closure){
            $tips_value = $option['tips']($data[$this->getPrimaryKey()]);
            $tips = ' <span class="badge">' . $tips_value . '</span>';
        }

        $attribute_html = $this->compileHtmlAttr($option['attribute']);
        $attribute_html = self::parseData($attribute_html, $data);
        return <<<HTML
<{$this->_button_dom_type} {$attribute_html}>{$option['attribute']['title']}{$tips}</{$this->_button_dom_type}>
HTML;
    }

    /**
     * 加入一按钮
     *
     * @param string $type 按钮类型，取值参考registerBaseRightButtonType
     * @param array|null  $attribute 按钮属性，一个定义标题/链接/CSS类名等的属性描述数组
     * @param string $tips 按钮提示
     * @param string|array $auth_node 按钮权限点
     * @param string|array|object $options 按钮options
     * @return array
     */
    public function genOneButton($type, $attribute = null, $tips = '', $auth_node = '', $options = []) {
        $button_option['type'] = $type;
        $button_option['attribute'] = $attribute;
        $button_option['tips'] = $tips;
        $button_option['auth_node'] = $auth_node;
        $button_option['options'] = $options;

        return $button_option;
    }

    public function setButtonDomType($type){
        $this->_button_dom_type = $type;
        return $this;
    }

    public function mergeAttr($def_attr, $cus_attr){
        $right_btn_def_class = $this->getBtnDefClass();
        $right_btn_def_class && $def_attr['class'] = $right_btn_def_class.' '.$def_attr['class'];
        return array_merge($def_attr, $cus_attr ?: []);
    }
}