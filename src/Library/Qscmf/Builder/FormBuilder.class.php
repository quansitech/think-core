<?php

namespace Qscmf\Builder;
use Bootstrap\RegisterContainer;
use Qscmf\Builder\FormType\Address\Address;
use Qscmf\Builder\FormType\Arr\Arr;
use Qscmf\Builder\FormType\AudioOss\AudioOss;
use Qscmf\Builder\FormType\Board\Board;
use Qscmf\Builder\FormType\Checkbox\Checkbox;
use Qscmf\Builder\FormType\City\City;
use Qscmf\Builder\FormType\Citys\Citys;
use Qscmf\Builder\FormType\Date\Date;
use Qscmf\Builder\FormType\Datetime\Datetime;
use Qscmf\Builder\FormType\District\District;
use Qscmf\Builder\FormType\Districts\Districts;
use Qscmf\Builder\FormType\Editormd\Editormd;
use Qscmf\Builder\FormType\File\File;
use Qscmf\Builder\FormType\Files\Files;
use Qscmf\Builder\FormType\Hidden\Hidden;
use Qscmf\Builder\FormType\Icon\Icon;
use Qscmf\Builder\FormType\Key\Key;
use Qscmf\Builder\FormType\Num\Num;
use Qscmf\Builder\FormType\Password\Password;
use Qscmf\Builder\FormType\Picture\Picture;
use Qscmf\Builder\FormType\PictureIntercept\PictureIntercept;
use Qscmf\Builder\FormType\PictureOss\PictureOss;
use Qscmf\Builder\FormType\PictureOssIntercept\PictureOssIntercept;
use Qscmf\Builder\FormType\Pictures\Pictures;
use Qscmf\Builder\FormType\PicturesIntercept\PicturesIntercept;
use Qscmf\Builder\FormType\PicturesOss\PicturesOss;
use Qscmf\Builder\FormType\PicturesOssIntercept\PicturesOssIntercept;
use Qscmf\Builder\FormType\Province\Province;
use Qscmf\Builder\FormType\Radio\Radio;
use Qscmf\Builder\FormType\Select\Select;
use Qscmf\Builder\FormType\Select2\Select2;
use Qscmf\Builder\FormType\SelectOther\SelectOther;
use Qscmf\Builder\FormType\Self\Self_;
use Qscmf\Builder\FormType\Static_\Static_;
use Qscmf\Builder\FormType\Tags\Tags;
use Qscmf\Builder\FormType\Text\Text;
use Qscmf\Builder\FormType\Textarea\Textarea;
use Qscmf\Builder\FormType\Ueditor\Ueditor;

/**
 * 表单页面自动生成器
 */
class FormBuilder extends BaseBuilder {
    private $_post_url;              // 表单提交地址
    private $_form_items = array();  // 表单项目
    private $_extra_items = array(); // 额外已经构造好的表单项目
    private $_form_data = array();   // 表单数据
    private $_ajax_submit = true;    // 是否ajax提交
    private $_custom_html;
    private $_form_item_Filter = null;
    private $_form_type = [];

    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize() {
        $module_name = 'Admin';
        $this->_template = __DIR__ .'/Layout/'.$module_name.'/form.html';

        self::registerFormType();
    }

    protected function registerFormType(){
        static $form_type = [];
        if(empty($form_type)) {
            $base_form_type = self::registerBaseFormType();
            $form_type = array_merge($base_form_type, RegisterContainer::getFormItems());
        }

        $this->_form_type = $form_type;
    }

    protected function registerBaseFormType(){
            return [
                'address' => Address::class,
                'array' => Arr::class,
                'ueditor' => Ueditor::class,
                'board' => Board::class,
                'checkbox' => Checkbox::class,
                'city' => City::class,
                'citys' => Citys::class,
                'date' => Date::class,
                'datetime' => Datetime::class,
                'district' => District::class,
                'districts' => Districts::class,
                'editormd' => Editormd::class,
                'file' => File::class,
                'files' => Files::class,
                'hidden' => Hidden::class,
                'icon' => Icon::class,
                'key' => Key::class,
                'num' => Num::class,
                'password' => Password::class,
                'picture' => Picture::class,
                'picture_intercept' => PictureIntercept::class,
                'pictures' => Pictures::class,
                'pictures_intercept' => PicturesIntercept::class,
                'province' => Province::class,
                'radio' => Radio::class,
                'select' => Select::class,
                'select2' => Select2::class,
                'select_other' => SelectOther::class,
                'self' => Self_::class,
                'static' => Static_::class,
                'tags' => Tags::class,
                'text' => Text::class,
                'textarea' => Textarea::class
            ];
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
    public function addFormItem($name, $type, $title = '', $tip = '', $options = array(), $extra_class = '', $extra_attr = '', $auth_node = []) {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['title'] = $title;
        $item['tip'] = $tip;
        $item['options'] = $options;
        $item['extra_class'] = $extra_class;
        $item['extra_attr'] = $extra_attr;
        $item['auth_node'] = $auth_node;
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
        $this->assign('form_builder_path', __DIR__ . '/formbuilder.html');

        parent::display($this->_template);
    }
}
