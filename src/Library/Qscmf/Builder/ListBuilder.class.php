<?php

namespace Qscmf\Builder;

use Bootstrap\RegisterContainer;
use Qscmf\Builder\ButtonType\Addnew\Addnew;
use Qscmf\Builder\ButtonType\Delete\Delete;
use Qscmf\Builder\ButtonType\Forbid\Forbid;
use Qscmf\Builder\ButtonType\Resume\Resume;
use Qscmf\Builder\ButtonType\Save\Save;
use Qscmf\Builder\ButtonType\Self\SelfButton;
use Qscmf\Builder\ListRightButton\Edit\Edit;
use Qscmf\Builder\ListSearchType\DateRange\DateRange;
use Qscmf\Builder\ListSearchType\Select\Select;
use Qscmf\Builder\ListSearchType\SelectCity\SelectCity;
use Qscmf\Builder\ListSearchType\SelectText\SelectText;
use Qscmf\Builder\ListSearchType\Text\Text;
use Qscmf\Builder\ListSearchType\Hidden\Hidden;

/**
 * 数据列表自动生成器
 */
class ListBuilder extends BaseBuilder {
    private $_top_button_list = array();   // 顶部工具栏按钮组
    private $_search  = array();           // 搜索参数配置
    private $_search_url;                    //搜索按钮指向url
    private $_table_column_list = array(); // 表格标题字段
    private $_table_data_list   = array(); // 表格数据列表
    private $_table_data_list_key = 'id';  // 表格数据列表主键字段名
    private $_table_data_page;             // 表格数据分页
    private $_right_button_list = array(); // 表格右侧操作按钮组
    private $_alter_data_list = array();   // 表格数据列表重新修改的项目
    private $_show_check_box = true;
    private $_meta_button_list = array();  //标题按钮
    private $_lock_row = 1; //锁定标题
    private $_lock_col = 0; //锁定列
    private $_page_template; // 页码模板
    private $_top_button_type = [];
    private $_search_type = [];
    private $_right_button_type = [];

    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize() {
        $module_name = 'Admin';
        $this->_template = __DIR__ .'/Layout/'.$module_name.'/list.html';
        $this->_page_template = __DIR__ .'/Layout/'.$module_name.'/pagination.html';

        self::registerTopButtonType();
        self::registerSearchType();
        self::registerRightButtonType();
    }

    public function getTableDataListKey(){
        return $this->_table_data_list_key;
    }

    public function setSearchUrl($url){
        $this->_search_url = $url;
        return $this;
    }

    public function setLockRow($row){
        $this->_lock_row = $row;
        return $this;
    }

    public function setLockCol($col){
        $this->_lock_col = $col;
        return $this;
    }

    public function setCheckBox($flag){
        $this->_show_check_box = $flag;
        return $this;
    }

    protected function registerSearchType(){
        static $search_type = [];
        if(empty($search_type)){
            $base_search_type = self::registerBaseSearchType();
            $search_type = array_merge($base_search_type, RegisterContainer::getListSearchType());
        }

        $this->_search_type = $search_type;
    }

    protected function registerBaseSearchType(){
        return [
            'date_range' => DateRange::class,
            'select' => Select::class,
            'select_city' => SelectCity::class,
            'select_text' => SelectText::class,
            'text' => Text::class,
            'hidden' => Hidden::class
        ];
    }

    protected function registerTopButtonType(){
        static $top_button_type = [];
        if(empty($top_button_type)) {
            $base_top_button_type = self::registerBaseTopButtonType();
            $top_button_type = array_merge($base_top_button_type, RegisterContainer::getListTopButtons());
        }

        $this->_top_button_type = $top_button_type;
    }

    protected function registerBaseTopButtonType(){
        return [
            'addnew' => Addnew::class,
            'delete' => Delete::class,
            'forbid' => Forbid::class,
            'resume' => Resume::class,
            'save' => Save::class,
            'self' => SelfButton::class
        ];
    }

    protected function registerRightButtonType(){
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
            'edit' => Edit::class,
            'delete' => \Qscmf\Builder\ListRightButton\Delete\Delete::class,
            'self' => \Qscmf\Builder\ListRightButton\Self\SelfButton::class
        ];
    }


    public function addMetaButton($type, $attribute = null, $tips = ''){
        switch ($type) {
            case 'return':  // 添加新增按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '返回';
                $my_attribute['class'] = 'btn btn-primary';
                $my_attribute['href']  = 'javascript:history.go(-1)';

                /**
                 * 如果定义了属性数组则与默认的进行合并
                 * 用户定义的同名数组元素会覆盖默认的值
                 * 比如$builder->addTopButton('add', array('title' => '换个马甲'))
                 * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
                 * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
                 */
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }
                $class = $my_attribute['class'];
                $my_attribute['class'] = 'pull-right ' . $class;
                // 这个按钮定义好了把它丢进按钮池里
                break;
            case 'self': //添加自定义按钮(第一原则使用上面预设的按钮，如果有特殊需求不能满足则使用此自定义按钮方法)
                // 预定义按钮属性以简化使用
                $my_attribute['class'] = 'btn btn-danger';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }
                $class = $my_attribute['class'];
                $my_attribute['class'] = 'pull-right ' . $class;
                // 这个按钮定义好了把它丢进按钮池里
                break;
        }
        if($tips != ''){
            $my_attribute['tips'] = $tips;
        }
        $this->_meta_button_list[] = $my_attribute;
        return $this;
    }

    /**
     * 加入一个列表顶部工具栏按钮
     * 在使用预置的几种按钮时，比如我想改变新增按钮的名称
     * 那么只需要$builder->addTopButton('add', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * @param string $type 按钮类型，主要有add/resume/forbid/recycle/restore/delete/self七几种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addTopButton($type, $attribute = null, $tips = '', $auth_node = '', $options = []) {

        $top_button_option['type'] = $type;
        $top_button_option['attribute'] = $attribute;
        $top_button_option['tips'] = $tips;
        $top_button_option['auth_node'] = $auth_node;
        $top_button_option['options'] = $options;

        $this->_top_button_list[] = $top_button_option;
        return $this;
    }


    public function addSearchItem($name, $type, $title='', $options = array()){
        $search_item = array(
            'name' => $name,
            'type' => $type,
            'title' => $title,
            'options' => $options
        );

        $this->_search[] = $search_item;
        return $this;
    }



    /**
     * 加一个表格标题字段
     */
    public function addTableColumn($name, $title, $type = null, $value = '', $editable = false, $tip = '') {
        $column = array(
            'name' => $name,
            'title' => $title,
            'editable' => $editable,
            'type' => $type,
            'value' => $value,
            'tip' => $tip
        );
        $this->_table_column_list[] = $column;
        return $this;
    }

    /**
     * 表格数据列表
     */
    public function setTableDataList($table_data_list) {
        $this->_table_data_list = $table_data_list;
        return $this;
    }

    /**
     * 表格数据列表的主键名称
     */
    public function setTableDataListKey($table_data_list_key) {
        $this->_table_data_list_key = $table_data_list_key;
        return $this;
    }

    /**
     * 加入一个数据列表右侧按钮
     * 在使用预置的几种按钮时，比如我想改变编辑按钮的名称
     * 那么只需要$builder->addRightpButton('edit', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * 因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
     * __data_id__会在display方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，edit/forbid/recycle/restore/delete/self六种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
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

    /**
     * 设置分页
     * @param $page
     * @return $this
     */
    public function setTableDataPage($table_data_page) {
        $this->_table_data_page = $table_data_page;
        return $this;
    }

    /**
     * 修改列表数据
     * 有时候列表数据需要在最终输出前做一次小的修改
     * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
     * @param $page
     * @return $this
     */
    public function alterTableData($condition, $alter_data) {
        $this->_alter_data_list[] = array(
            'condition' => $condition,
            'alter_data' => $alter_data
        );
        return $this;
    }


    /**
     * 显示页面
     */
    public function display() {
        // 编译data_list中的值
        foreach ($this->_table_data_list as &$data) {
            // 编译表格右侧按钮
            if ($this->_right_button_list) {

                $right_button_list = [];
                foreach ($this->_right_button_list as $right_button) {

                    //是否存在权限控制，存在则检验有无权限值
                    if($right_button['auth_node'] && !verifyAuthNode($right_button['auth_node'])) {
                        continue;
                    }

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

                    if($right_button['options']){
                        $json_options = json_encode($right_button['options']);

                        while(preg_match('/__(.+?)__/i', $json_options, $matches)){
                            $json_options = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $json_options);
                        }

                        $right_button['options'] = json_decode($json_options, true);
                    }

                    $tmp = [];
                    $content = (new $this->_right_button_type[$right_button['type']]())->build($right_button, $data, $this);
                    $button_html = self::compileRightButton($right_button, $data);
                    $tmp = <<<HTML
{$button_html}&nbsp;
{$content}
HTML;
                    $right_button_list[] = $tmp;

                }

                $data['right_button'] = join(' ', $right_button_list);
            }

            // 根据表格标题字段指定类型编译列表数据
            foreach ($this->_table_column_list as &$column) {
                switch ($column['type']) {
                    case 'status':
                        switch($data[$column['name']]){
                            case '0':
                                $data[$column['name']] = '<i class="fa fa-ban text-danger"></i>';
                                break;
                            case '1':
                                $data[$column['name']] = '<i class="fa fa-check text-success"></i>';
                                break;
                        }
                        break;
                    case 'icon':
                        $data[$column['name']] = '<i class="'.$data[$column['name']].'"></i>';
                        break;
                    case 'date':
                        $data[$column['name']] = time_format($data[$column['name']], 'Y-m-d');
                        break;
                    case 'time':
                        $data[$column['name']] = time_format($data[$column['name']]);
                        break;
                    case 'picture':
                        $data[$column['name']] = '<img src="'.showFileUrl($data[$column['name']]).'">';
                        break;
                    case 'type':
                        $form_item_type = C('FORM_ITEM_TYPE');
                        $data[$column['name']] = $form_item_type[$data[$column['name']]][0];
                        break;
                    case 'fun':
                        if(preg_match('/(.+)->(.+)\((.+)\)/', $column['value'], $object_matches)){
                            $object_matches[3] = str_replace('\'', '', $object_matches[3]);
                            $object_matches[3] = str_replace('"', '', $object_matches[3]);
                            $object_matches[3] = str_replace('__data_id__', $data[$column['name']], $object_matches[3]);
                            $object_matches[3] = str_replace('__id__', $data[$this->_table_data_list_key], $object_matches[3]);
                            $param_arr = explode(',', $object_matches[3]);
                            if(preg_match('/(.+)\((.+)\)/', $object_matches[1], $object_func_matches)){
                                $object_func_matches[2] = str_replace('\'', '', $object_func_matches[2]);
                                $object_func_matches[2] = str_replace('"', '', $object_func_matches[2]);
                                $object_param_arr = explode(',', $object_func_matches[2]);
                                $object = call_user_func_array($object_func_matches[1], $object_param_arr);
                                $data[$column['name']] = call_user_func_array(array($object, $object_matches[2]), $param_arr);
                            }
                        }
                        else if(preg_match('/(.+)\((.+)\)/', $column['value'], $func_matches)){
                            $func_matches[2] = str_replace('\'', '', $func_matches[2]);
                            $func_matches[2] = str_replace('"', '', $func_matches[2]);
                            $func_matches[2] = str_replace('__data_id__', $data[$column['name']], $func_matches[2]);
                            $func_matches[2] = str_replace('__id__', $data[$this->_table_data_list_key], $func_matches[2]);
                            $func_param_arr = explode(',', $func_matches[2]);
                            $data[$column['name']] = call_user_func_array($func_matches[1], $func_param_arr);
                        }
                        break;
                    case 'a': //A标签 fun对应的就是A标签的属性
                        $a_str = '<a ' . $this->compileHtmlAttr($column['value']) . ' >' . $data[$column['name']] . '</a>';
                        if(preg_match('/__(.+?)__/', $a_str, $matches)){
                            $a_str = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $a_str);
                        }
                        $data[$column['name']] = $a_str;
                        break;
                    case 'self':
                        $html = $column['value'];
                        while(preg_match('/__(.+?)__/i', $html, $matches)){
                            $html = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $html);
                        }
                        $data[$column['name']] = $html;
                        break;
                }
            }

            /**
             * 修改列表数据
             * 有时候列表数据需要在最终输出前做一次小的修改
             * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
             */
            if ($this->_alter_data_list) {
                foreach ($this->_alter_data_list as $alter) {
                    if ($data[$alter['condition']['key']] === $alter['condition']['value']) {
                        //寻找alter_data里需要替代的变量
                        foreach($alter['alter_data'] as $key => $val){
                            while(preg_match('/.+\{\$(.+)\}.+/i', $val, $matches)){
                                $val = str_replace('{$' . $matches[1] . '}', $data[$matches[1]], $val);
                            }
                            $alter['alter_data'][$key] = $val;
                        }
                        $data = array_merge($data, $alter['alter_data']);
                    }
                }
            }
        }

        //编译top_button_list中的HTML属性
        if ($this->_top_button_list) {
            $top_button_list = [];
            foreach ($this->_top_button_list as $option) {
                if($option['auth_node'] && !verifyAuthNode($option['auth_node'])) {
                    continue;
                }
                $tmp = [];
                $content = (new $this->_top_button_type[$option['type']]())->build($option);
                $button_html = self::compileTopButton($option);
                $tmp['render_content'] = <<<HTML
{$button_html}
{$content}
HTML;
                $top_button_list[] = $tmp;
            }
            $this->_top_button_list = $top_button_list;
        }

        if ($this->_meta_button_list) {
            foreach ($this->_meta_button_list as &$button) {
                $this->compileButton($button);
            }
        }

        foreach($this->_search as &$search){
            $search['render_content'] = (new $this->_search_type[$search['type']]())->build($search);
        }

        $this->assign('meta_title',          $this->_meta_title);          // 页面标题
        $this->assign('top_button_list',     $this->_top_button_list);     // 顶部工具栏按钮
        $this->assign('meta_button_list',     $this->_meta_button_list);
        $this->assign('search',              $this->_search);              // 搜索配置
        $this->assign('tab_nav',             $this->_tab_nav);             // 页面Tab导航
        $this->assign('table_column_list',   $this->_table_column_list);   // 表格的列
        $this->assign('table_data_list',     $this->_table_data_list);     // 表格数据
        $this->assign('table_data_list_key', $this->_table_data_list_key); // 表格数据主键字段名称
        $this->assign('pagination',     $this->_table_data_page);     // 表示个数据分页
        $this->assign('right_button_list',   $this->_right_button_list);   // 表格右侧操作按钮
        $this->assign('alter_data_list',     $this->_alter_data_list);     // 表格数据列表重新修改的项目
        $this->assign('extra_html',          $this->_extra_html);          // 额外HTML代码
        $this->assign('top_html',            $this->_top_html);            // 顶部自定义html代码
        $this->assign('page_template',       $this->_page_template);       // 页码模板自定义html代码
        $this->assign('show_check_box', $this->_show_check_box);
        $this->assign('nid', $this->_nid);
        $this->assign('lock_row', $this->_lock_row);
        $this->assign('lock_col', $this->_lock_col);
        $this->assign('search_url', $this->_search_url);
        $this->assign('list_builder_path', __DIR__ . '/listbuilder.html');
        parent::display($this->_template);
    }

    protected function compileTopButton($option){
        if($option['tips'] != ''){
            $tips_html = '<span class="badge">' . $option['tips'] . '</span>';
        }

        return <<<HTML
<a {$this->compileHtmlAttr($option['attribute'])}>{$option['attribute']['title']} {$tips_html}</a>
HTML;
    }

    protected function compileRightButton($option, $data){
        // 将约定的标记__data_id__替换成真实的数据ID
        $option['attribute']['href'] = preg_replace(
            '/__data_id__/i',
            $data[$this->_table_data_list_key],
            $option['attribute']['href']
        );

        //将data-id的值替换成真实数据ID
        $option['attribute']['data-id'] = preg_replace(
            '/__data_id__/i',
            $data[$this->_table_data_list_key],
            $option['attribute']['data-id']
        );

        $tips = '';
        if($option['tips'] && is_string($option['tips'])){
            $tips = '<span class="badge">' . $option['tips'] . '</span>';
        }
        else if($option['tips'] && $option['tips'] instanceof \Closure){
            $tips_value = $option['tips']($data[$this->_table_data_list_key]);
            $tips = '<span class="badge">' . $tips_value . '</span>';
        }

        $attribute_html = $this->compileHtmlAttr($option['attribute']);

        while(preg_match('/__(.+?)__/i', $attribute_html, $matches)){
            $attribute_html = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $attribute_html);
        }

        return <<<HTML
<a {$attribute_html}>{$option['attribute']['title']} {$tips}</a>
HTML;
    }

    protected function compileButton(&$button){
        $button['attribute'] = $this->compileHtmlAttr($button);
        if($button['tips'] != ''){
            $button['tips'] = '<span class="badge">' . $button['tips'] . '</span>';
        }
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


    /**
     * 设置页码模版
     * @param $page_template 页码模版自定义html代码
     * @return $this
     */
    public function setPageTemplate($page_template) {
        $this->_page_template = $page_template;
        return $this;
    }
}
