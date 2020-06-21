<?php

namespace Qscmf\Builder;
use Qscmf\Builder\FormType\FormTypeRegister;

/**
 * 表单页面自动生成器
 */
class FormBuilder extends BaseBuilder {
    use FormTypeRegister;

    private $_post_url;              // 表单提交地址
    private $_form_items = array();  // 表单项目
    private $_extra_items = array(); // 额外已经构造好的表单项目
    private $_form_data = array();   // 表单数据
    private $_ajax_submit = true;    // 是否ajax提交
    private $_custom_html;
    private $_form_item_Filter = null;
    private $_readonly = false;


    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize() {
        $module_name = 'Admin';
        $this->_template = __DIR__ .'/Layout/'.$module_name.'/form.html';

        self::registerFormType();
    }

    public function setReadOnly($readonly){
        $this->_readonly = $readonly;
        return $this;
    }

    public function setFormType($type_name, $type_cls){
        $this->_form_type[$type_name] = $type_cls;
    }

    public function setCustomHtml($custom_html){
        $this->_custom_html = $custom_html;
        return $this;
    }

    /**
     * 直接设置表单项数组
     * @param $form_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items) {
        $this->_extra_items = $extra_items;
        return $this;
    }

    /**
     * 设置表单提交地址
     * @param $url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url) {
        $this->_post_url = $post_url;
        return $this;
    }

    public function setFormItemFilter(\Closure $filter){
        $this->_form_item_Filter = $filter;
        return $this;
    }

    /**
     * 加入一个表单项
     * @param string $name item名
     * @param string $type item类型(取值参考系统配置FORM_ITEM_TYPE)
     * @param string $title item标题
     * @param string $tip item提示说明
     * @param array $options item options
     * @param string $extra_class item项额外样式，如使用hidden则隐藏item
     * @param string $extra_attr item项额外属性
     * @param string|array $auth_node item权限点
     * @return $this
     */
    public function addFormItem($name, $type, $title = '', $tip = '', $options = array(), $extra_class = '', $extra_attr = '', $auth_node = [], $item_option = []) {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['title'] = $title;
        $item['tip'] = $tip;
        $item['options'] = $options;
        $item['extra_class'] = $extra_class;
        $item['extra_attr'] = $extra_attr;
        $item['auth_node'] = $auth_node;
        $item['item_option'] = $item_option;
        $this->_form_items[] = $item;
        return $this;
    }

    /**
     * 设置表单表单数据
     * @param $form_data 表单数据
     * @return $this
     */
    public function setFormData($form_data) {
        $this->_form_data = $form_data;
        return $this;
    }

    /**
     * 设置提交方式
     * @param $title 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true) {
        $this->_ajax_submit = $ajax_submit;
        return $this;
    }

    /**
     * 显示页面
     */
    public function display() {
        //额外已经构造好的表单项目与单个组装的的表单项目进行合并
        $this->_form_items = array_merge($this->_form_items, $this->_extra_items);

        //编译表单值

        foreach ($this->_form_items as &$item) {
            if ($this->_form_data) {
                if (isset($this->_form_data[$item['name']])) {
                    $item['value'] = $this->_form_data[$item['name']];
                }
            }

            if($this->_readonly){
                $item['item_option'] = array_merge($item['item_option'], ['read_only' => true]);
            }

            $item['render_content'] = (new $this->_form_type[$item['type']]())->build($item);
        }

        if($this->_form_item_Filter){
            $this->_form_items = call_user_func($this->_form_item_Filter, $this->_form_data, $this->_form_items);
        }

        // 检测字段的权限点，无权限则unset该item
        $this->_form_items = $this->checkAuthNode($this->_form_items);

        $this->assign('custom_html', $this->_custom_html);
        $this->assign('meta_title',  $this->_meta_title);  //页面标题
        $this->assign('tab_nav',     $this->_tab_nav);     //页面Tab导航
        $this->assign('post_url',    $this->_post_url);    //标题提交地址
        $this->assign('form_items',  $this->_form_items);  //表单项目
        $this->assign('ajax_submit', $this->_ajax_submit); //是否ajax提交
        $this->assign('extra_html',  $this->_extra_html);  //额外HTML代码
        $this->assign('top_html',    $this->_top_html);    //顶部自定义html代码
        $this->assign('form_data', $this->_form_data);
        $this->assign('nid', $this->_nid);
        $this->assign('read_only', $this->_readonly);
        $this->assign('form_builder_path', __DIR__ . '/formbuilder.html');

        parent::display($this->_template);
    }
}
