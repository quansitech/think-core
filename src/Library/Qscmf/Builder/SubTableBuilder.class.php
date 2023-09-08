<?php
namespace Qscmf\Builder;


use Org\Util\StringHelper;
use Qscmf\Builder\ButtonType\Save\Save;
use Think\View;

class SubTableBuilder implements \Qscmf\Builder\GenColumn\IGenColumn {
    use \Qscmf\Builder\GenColumn\TGenColumn;

    private $_table_header = [];
    private $_items = [];
    private $_template;
    private $_unique_id;
    private $_data = [];
    private $_hide_btn;
    private $_set_add_btn;
    private $_col_readonly = false;
    private $_table_column_list = array(); // 表格标题字段
    private $_new_row_position;
    private array $_exists_column_name = [];
    private array $_add_row_default = []; // 新增一行时的默认值

    const NEW_ROW_AT_FIRST = 'first';
    const NEW_ROW_AT_LAST = 'last';

    public function __construct($hide_btn=false){
        $this->_template = __DIR__ . '/subTableBuilder.html';
        $this->_unique_id = StringHelper::keyGen();
        $this->_hide_btn=$hide_btn;
        $this->_new_row_position = self::NEW_ROW_AT_LAST;
        self::registerColumnType();
    }

    protected function unsetSaveMark(){
        Save::$target_form = "";
    }

    public function addTableHeader($name, $width, $tip=''){
        $header['name'] = $name;
        $header['width'] = $width;
        $header['tip'] = $tip;
        $this->_table_header[] = $header;
        return $this;
    }

    protected function appendColumnName($name):self|\Exception{
        if (!in_array($name, $this->_exists_column_name)){
            $this->_exists_column_name[] = $name;
            return $this;
        }else{
            E($name. " 该字段已存在");
        }
    }

    public function addFormItem($name, $type, $options = [],$readonly=false,$extra_class='',$extra_attr='',
                                $auth_node = '') {
        $this->appendColumnName($name);

        $item['name'] = $name;
        $item['type'] = $type;
        $item['options'] = $options;
        $item['readonly'] = $readonly;
        $item['extra_class'] = $extra_class;
        $item['extra_attr'] = $extra_attr;
        $item['auth_node'] = $auth_node;

        $this->_items[] = $item;
        return $this;
    }

    public function setData($data, $has_col_options = true) {
        if ($has_col_options === true){
            $this->setDataWithColOptions($data);
        }else{
            $this->setFormData($data);
        }

        return $this;
    }

    protected function setDataWithColOptions($data){
        $form_data = array_map(function ($item){
            return array_column($item, 'value', 'name');
        }, $data);
        $this->_data = $form_data;
        return $this;
    }

    public function setFormData($data) {
        $this->_data = $data;
        return $this;
    }

    public function addRowDefault($data) {
        $this->_add_row_default = $data;
        return $this;
    }

    public function setAddBtn($set_add_btn){
        $this->_set_add_btn = $set_add_btn;
        return $this;
    }

    public function makeHtml(){
        self::combinedColumnOptions();

        $this->unsetSaveMark();

        $this->_table_column_list = $this->checkAuthNode($this->_table_column_list);

        $this->genPerRowWithData($this->_data);

        $view = new View();
        $view->assign('table_header', $this->_table_header);   // 表格的列
        $view->assign('table_column_list', $this->_table_column_list);   // 表格的列
        $view->assign('table_id', $this->_unique_id);
        $view->assign('data', $this->_data);
        $view->assign('hide_btn', $this->_hide_btn);
        $view->assign('set_add_btn', $this->_set_add_btn);
        $view->assign('column_html', $this->buildRows($this->_data));
        $view->assign('column_css_and_js_str', $this->getUniqueColumnCssAndJs());
        $view->assign('new_row_pos', $this->_new_row_position);

        return $view->fetch($this->_template);
    }

    protected function checkAuthNode($check_items){
        return BuilderHelper::checkAuthNode($check_items);
    }

    private function combinedColumnOptions(){
        foreach ($this->_items as $item){
            $item['readonly'] = $this->_col_readonly ?:$item['readonly'];

            $col_arr = [
                'name' => $item['name'],
                'title' => '',
                'editable' => !$item['readonly'],
                'type' => $item['type'],
                'value' => $item['options'],
                'tip' => null,
                'th_extra_attr' => null,
                'td_extra_attr' => null,
                'auth_node' => $item['auth_node'],
                'extra_class' => $item['extra_class'],
                'extra_attr' => $item['extra_attr'],
            ];

            extract($col_arr);
            $column = self::genOneColumnOpt($name,$title,$type,$value,$editable,$tip,$th_extra_attr,$td_extra_attr,
                $auth_node,$extra_attr,$extra_class);

            $column['add_row_default'] = $this->_add_row_default[$item['name']] ?? '';

            $this->_table_column_list[] = $column;
        }
    }

    protected function initData($column_options){
        $column_data[] = collect($column_options)->mapWithKeys(function ($item){
            return [$item['name'] => $item['add_row_default'] ?? ''];
        })->all();

        return $column_data;
    }

    protected function genPerRowWithData(&$column_data = [], $column_options = []){
        $column_options = $column_options?:$this->_table_column_list;
        $column_data = !empty($column_data) ? $column_data : self::initData($column_options);

        foreach ($column_data as &$data){
            foreach ($column_options as $k => &$column) {
                $this->buildOneColumnItem($column, $data);
            }
        }
    }

    public function genNewRowHtml($options = []){
        $this->genPerRowWithData($column_data, $options);
        $html = $this->buildOneRow($column_data[0], $options);

        return $html;
    }

    protected function buildOneRow($item_data, $options = []){
        $html = '<tr class="data-row">';
        foreach ($options as $k => $column){
            $html .= "<td {$column['td_extra_attr']} class='sub_item_{$column['type']}'>{$item_data[$column['name']]}</td>";
        }
        !$this->_hide_btn && $html .= "<td> <button type='button' class='btn btn-danger btn-sm' onclick="."$(this).parents('tr').remove();".">删除</button> </td>";
        $html .= '</tr>';

        return $html;
    }

    protected function buildRows($column_data, $options = []){
        $options = $options ?: $this->_table_column_list;

        $html = '';

        foreach($column_data as $item_data){
            $html .= $this->buildOneRow($item_data, $options);
        }

        if($this->_hide_btn){
            return $html;
        }

        if($this->_new_row_position === self::NEW_ROW_AT_LAST){
            $html = $html . $this->addButtonHtml();
        }

        if($this->_new_row_position === self::NEW_ROW_AT_FIRST){
            $html = $this->addButtonHtml() . $html;
        }

        return $html;
    }

    protected function addButtonHtml() : string{
        $header_count = count($this->_table_header) + 1;
        $btn_text = $this->_set_add_btn ?: '添加新字段';
        $html = <<<html
<tr id="{$this->_unique_id}_add-panel">
    <td colspan="{$header_count}" class="text-center">
        <span class="pull-left tip text-danger"></span>
        <button type="button" class="btn btn-sm btn-default " id="{$this->_unique_id}_addField">{$btn_text}</button>
    </td>
</tr>
html;
        return $html;

    }

    public function setColReadOnly($col_readonly){
        $this->_col_readonly = $col_readonly;

        return $this;
    }

    public function setNewRowPos(string $position){
        $this->_new_row_position = $position;

        return $this;
    }
}