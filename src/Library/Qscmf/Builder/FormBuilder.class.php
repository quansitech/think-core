<?php

namespace Qscmf\Builder;

use Illuminate\Support\Str;
use Qscmf\Builder\Antd\BuilderAdapter\FormAdapter;
use Qscmf\Builder\Antd\HasAntdRender;
use Qscmf\Builder\FormType\FormTypeRegister;
use Qscmf\Builder\Validator\TValidator;

/**
 * 表单页面自动生成器
 */
class FormBuilder extends BaseBuilder implements \Qscmf\Builder\GenButton\IGenButton
{
    use FormTypeRegister;
    use \Qscmf\Builder\GenButton\TGenButton;
    use TValidator;
    use HasAntdRender;

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
    private $_form_data_key = 'id';  // 数据主键字段名
    private $_primary_key = '_pk';  // 备份主键
    private $_btn_def_class = 'qs-form-btn';
    private $_button_list;

    private $_submit_btn_title = '确定';

    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize()
    {
        $module_name = 'Admin';
        $this->_template = __DIR__ . '/Layout/' . $module_name . '/form.html';
        $this->_form_template = __DIR__ . '/formbuilder.html';

        self::registerFormType();
        self::registerButtonType();

        $this->setGid(Str::uuid()->getHex());
    }

    public function setReadOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    public function setFormType($type_name, $type_cls)
    {
        $this->_form_type[$type_name] = $type_cls;
    }

    public function setCustomHtml($custom_html)
    {
        $this->_custom_html = $custom_html;
        return $this;
    }

    public function addBottom($html)
    {
        array_push($this->_bottom, $html);
        return $this;
    }

    /**
     * 直接设置表单项数组
     * @param $form_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items)
    {
        $this->_extra_items = $extra_items;
        return $this;
    }

    /**
     * 设置表单提交地址
     * @param $url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url)
    {
        $this->_post_url = $post_url;
        return $this;
    }

    public function setFormItemFilter(\Closure $filter)
    {
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
    public function addFormItem($name, $type, $title = '', $tip = '', $options = array(), $extra_class = '', $extra_attr = '', $auth_node = [], $item_option = [])
    {
        $this->appendColumnName($name);

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
    public function setFormData($form_data)
    {
        $this->_form_data = $form_data;
        return $this;
    }

    /**
     * 设置提交方式
     * @param $title 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true)
    {
        $this->_ajax_submit = $ajax_submit;
        return $this;
    }

    public function setSubmitBtnTitle($title)
    {
        $this->_submit_btn_title = $title;
        return $this;
    }

    public function setDataKeyName($form_data_key)
    {
        $this->_form_data_key = $form_data_key;
        return $this;
    }

    public function getDataKeyName()
    {
        return $this->_form_data_key;
    }

    protected function backupPk()
    {
        $this->_form_data[$this->_primary_key] = $this->_form_data[$this->_form_data_key];
    }

    public function getPrimaryKey()
    {
        return $this->_primary_key;
    }

    public function getBtnDefClass()
    {
        return $this->_btn_def_class;
    }

    /**
     * 加入一按钮
     * 在使用预置的几种按钮时，比如我想改变编辑按钮的名称
     * 那么只需要$builder->addButton('edit', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * 因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
     * __data_id__会在display方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，取值参考registerBaseRightButtonType
     * @param array|null $attribute 按钮属性，一个定义标题/链接/CSS类名等的属性描述数组
     * @param string $tips 按钮提示
     * @param string|array $auth_node 按钮权限点
     * @param string|array|object $options 按钮options
     * @return $this
     */
    public function addButton($type, $attribute = null, $tips = '', $auth_node = '', $options = [])
    {
        $this->_button_list[] = $this->genOneButton($type, $attribute, $tips, $auth_node, $options);
        return $this;
    }

    /**
     * @deprecated 已在v13版本删除， 请使用 build 代替
     * 显示页面
     */
    public function display($render = false, $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        E("display method is delete,use build instead");
    }

    protected function beforeBuild()
    {
        $this->backupPk();

        //额外已经构造好的表单项目与单个组装的的表单项目进行合并
        $this->_form_items = array_merge($this->_form_items, $this->_extra_items);
        $this->_button_list = $this->checkAuthNode($this->_button_list);

        if ($this->_button_list) {
            self::setButtonDomType('button');
            $this->_form_data['button_list'] = join('', self::parseButtonList($this->_button_list, $this->_form_data));
        }
        //编译表单值

        foreach ($this->_form_items as &$item) {
            if ($this->_form_data) {
                if (isset($this->_form_data[$item['name']])) {
                    $item['value'] = $this->_form_data[$item['name']];
                }
            }

            if ($this->_readonly) {
                $item['item_option'] = array_merge($item['item_option'], ['read_only' => true]);
            }

            $item['render_content'] = (new $this->_form_type[$item['type']]())->build($item);
        }

        if ($this->_form_item_Filter) {
            $this->_form_items = call_user_func($this->_form_item_Filter, $this->_form_data, $this->_form_items);
        }

        // 检测字段的权限点，无权限则unset该item
        $this->_form_items = $this->checkAuthNode($this->_form_items);

        if (!empty($this->_bottom)) {
            //保持底部按钮与内容块保持间隔
            array_push($this->_bottom, '<br />');
        }

        $this->assign('custom_html', $this->_custom_html);
        $this->assign('meta_title', $this->_meta_title);  //页面标题
        $this->assign('tab_nav', $this->_tab_nav);     //页面Tab导航
        $this->assign('post_url', $this->_post_url);    //标题提交地址
        $this->assign('form_items', $this->_form_items);  //表单项目
        $this->assign('ajax_submit', $this->_ajax_submit); //是否ajax提交
        $this->assign('extra_html', $this->_extra_html);  //额外HTML代码
        $this->assign('top_html', $this->_top_html);    //顶部自定义html代码
        $this->assign('form_data', $this->_form_data);
        $this->assign('nid', $this->_nid);
        $this->assign('read_only', $this->_readonly);
        $this->assign('bottom_html', join('', $this->_bottom));
        $this->assign('show_btn', $this->_show_btn);
        $this->assign('form_builder_path', $this->_form_template);
        $this->assign('button_list', $this->_button_list);
        $this->assign('content_bottom_html', join('', $this->_content_bottom));
        $this->assign('submit_btn_title', $this->_submit_btn_title);
        $this->assign('gid', $this->getGid());
        $this->assign('validator', $this->getValidateList());
        $this->assign('need_validate', $this->needValidate() ? 1 : '');
    }

    public function antdRender($render = true): string|FormAdapter|array
    {
        $this->beforeBuild();

        $adapter = new FormAdapter($this);
        if ($render) {
            return $adapter->render();
        }
        return $adapter;
    }

    public function build($render = false)
    {
        if (C('ANTD_ADMIN_BUILDER_ENABLE')) {
            return $this->antdRender(!$render);
        }

        $this->beforeBuild();

        if ($render) {
            return parent::fetch($this->_form_template);
        } else {
            parent::display($this->_template);
        }
    }

    public function mergeAttr($def_attr, $cus_attr)
    {
        $right_btn_def_class = $this->getBtnDefClass();
        $right_btn_def_class && $def_attr['class'] = $right_btn_def_class . ' ' . $def_attr['class'];
        $def_attr['type'] = 'button';
        return array_merge($def_attr, $cus_attr ?: []);
    }

    public function setShowBtn($is_show = true)
    {
        $this->_show_btn = $is_show;
        return $this;
    }
}
