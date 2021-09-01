<?php

namespace Qscmf\Builder;
use Qscmf\Builder\FormType\FormTypeRegister;

/**
 * 表单页面自动生成器
 */
class FormBuilder extends BaseBuilder implements  \Qscmf\Builder\ListRightButton\RightButtonInterface {
    use FormTypeRegister;
    use \Qscmf\Builder\ListRightButton\RightButtonTrait;

    private $_post_url;              // 表单提交地址
    private $_form_items = array();  // 表单项目
    private $_extra_items = array(); // 额外已经构造好的表单项目
    private $_form_data = array();   // 表单数据
    private $_ajax_submit = true;    // 是否ajax提交
    private $_custom_html;
    private $_form_item_Filter = null;
    private $_readonly = false;
    private $_bottom = [];
    private $_show_btn = true; // 是否展示按钮
    private $_form_template;
    private $_table_data_list_key = 'id';  // 数据主键字段名
    private $_primary_key = '_pk';  // 备份主键
    private $_btn_def_class = 'qs-form-btn';

    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize() {
        $module_name = 'Admin';
        $this->_template = __DIR__ .'/Layout/'.$module_name.'/form.html';
        $this->_form_template = __DIR__ . '/formbuilder.html';

        self::registerFormType();
        self::registerRightButtonType();
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

    public function addBottom($html){
        array_push($this->_bottom, $html);
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

    public function setTableDataListKey($table_data_list_key) {
        $this->_table_data_list_key = $table_data_list_key;
        return $this;
    }

    public function getTableDataListKey()
    {
        return $this->_table_data_list_key;
    }

    protected function backupPk(){
        $this->_form_data[$this->_primary_key] = $this->_form_data[$this->_table_data_list_key];
    }

    public function getPrimaryKey(){
        return $this->_primary_key;
    }

    public function getRightBtnDefClass(){
        return $this->_btn_def_class;
    }

    /**
     * 显示页面
     */
    public function display($render = false) {
        $this->backupPk();

        //额外已经构造好的表单项目与单个组装的的表单项目进行合并
        $this->_form_items = array_merge($this->_form_items, $this->_extra_items);
        $this->_right_button_list = $this->checkAuthNode($this->_right_button_list);

        if ($this->_right_button_list) {
            self::setRightButtonDomType('button');
            $this->_form_data['right_button'] = join('',self::genButtonList($this->_form_data));
        }
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

        if(!empty($this->_bottom)){
            //保持底部按钮与内容块保持间隔
            array_push($this->_bottom, '<br />');
        }

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
        $this->assign('bottom_html', join('', $this->_bottom));
        $this->assign('show_btn', $this->_show_btn);
        $this->assign('form_builder_path', $this->_form_template);
        $this->assign('button_list', $this->_right_button_list);

        if($render){
            return parent::fetch($this->_form_template);
        }
        else{
            parent::display($this->_template);
        }
    }

    public function setShowBtn($is_show = true){
        $this->_show_btn = $is_show;
        return $this;
    }
}
