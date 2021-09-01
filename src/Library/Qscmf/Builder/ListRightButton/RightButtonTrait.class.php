<?php

namespace Qscmf\Builder\ListRightButton;

use Bootstrap\RegisterContainer;

trait RightButtonTrait
{
    private $_right_button_type;
    private $_right_button_list;
    private $_right_button_dom_type = 'a';

    public function registerRightButtonType(){
        static $right_button_type = [];
        if(empty($right_button_type)) {
            $base_right_button_type = self::registerBaseRightButtonType();
            $right_button_type = array_merge($base_right_button_type, RegisterContainer::getListRightButtonType());
        }

        $this->_right_button_type = $right_button_type;
    }

    protected function registerBaseRightButtonType(){

        return [
            'forbid' => \Qscmf\Builder\ListRightButton\Forbid\Forbid::class,
            'edit' => \Qscmf\Builder\ListRightButton\Edit\Edit::class,
            'delete' => \Qscmf\Builder\ListRightButton\Delete\Delete::class,
            'self' => \Qscmf\Builder\ListRightButton\Self\SelfButton::class
        ];
    }

    public function genButtonList(&$data){
        $right_button_list = [];
        foreach ($this->_right_button_list as $right_button) {

            if(isset($right_button['attribute']['{key}']) && isset($right_button['attribute']['{condition}']) && isset($right_button['attribute']['{value}'])){
                $continue_flag = false;
                switch($right_button['attribute']['{condition}']){
                    case 'eq':
                        if($data[$right_button['attribute']['{key}']] != $right_button['attribute']['{value}']){
                            $continue_flag = true;
                        }
                        break;
                    case 'neq':
                        if($data[$right_button['attribute']['{key}']] == $right_button['attribute']['{value}']){
                            $continue_flag = true;
                        }
                        break;
                }
                if($continue_flag){
                    continue;
                }
                unset($right_button['attribute']['{key}']);
                unset($right_button['attribute']['{condition}']);
                unset($right_button['attribute']['{value}']);
            }

            if($right_button['options'] && !is_object($right_button['options']) ){
                $json_options = json_encode($right_button['options']);
                $json_options = $this->parseData($json_options, $data);
                $right_button['options'] = json_decode($json_options, true);
            }

            if(isset($right_button['attribute']['title']) && empty($right_button['attribute']['title'])){
                unset($right_button['attribute']['title']);
            }
            $content = (new $this->_right_button_type[$right_button['type']]())->build($right_button, $data, $this);
            $button_html = self::compileRightButton($right_button, $data);
            $tmp = <<<HTML
{$button_html}&nbsp;
{$content}
HTML;
            $right_button_list[] = $tmp;

        }

        return $right_button_list;
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
<{$this->_right_button_dom_type} {$attribute_html}>{$option['attribute']['title']}{$tips}</{$this->_right_button_dom_type}>
HTML;
    }

    /**
     * 加入一按钮
     * 在使用预置的几种按钮时，比如我想改变编辑按钮的名称
     * 那么只需要$builder->addRightButton('edit', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * 因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
     * __data_id__会在display方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，取值参考registerBaseRightButtonType
     * @param array|null  $attribute 按钮属性，一个定义标题/链接/CSS类名等的属性描述数组
     * @param string $tips 按钮提示
     * @param string|array $auth_node 按钮权限点
     * @param string|array $options 按钮options
     * @return $this
     */
    public function addRightButton($type, $attribute = null, $tips = '', $auth_node = '', $options = []) {
        $right_button_option['type'] = $type;
        $right_button_option['attribute'] = $attribute;
        $right_button_option['tips'] = $tips;
        $right_button_option['auth_node'] = $auth_node;
        $right_button_option['options'] = $options;

        $this->_right_button_list[] = $right_button_option;
        return $this;
    }

    public function setRightButtonDomType($type){
        $this->_right_button_dom_type = $type;
        return $this;
    }

    public function mergeAttr($def_attr, $cus_attr){
        $right_btn_def_class = $this->getRightBtnDefClass();
        $right_btn_def_class && $def_attr['class'] = $right_btn_def_class.' '.$def_attr['class'];
        return array_merge($def_attr, $cus_attr ?: []);
    }
}