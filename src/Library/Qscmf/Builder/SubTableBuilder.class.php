<?php
namespace Qscmf\Builder;


use Org\Util\StringHelper;
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

    public function __construct($hide_btn=false){
        $this->_template = __DIR__ . '/subTableBuilder.html';
        $this->_unique_id = StringHelper::keyGen();
        $this->_hide_btn=$hide_btn;
        self::registerColumnType();
    }

    public function addTableHeader($name, $width, $tip){
        $header['name'] = $name;
        $header['width'] = $width;
        $header['tip'] = $tip;
        $this->_table_header[] = $header;
        return $this;
    }

    public function addFormItem($name, $type, $options = [],$readonly=false,$extra_class='',$extra_attr='',
                                $auth_node = '') {

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

    public function setAddBtn($set_add_btn){
        $this->_set_add_btn = $set_add_btn;
        return $this;
    }

    public function makeHtml(){
        self::combinedColumnOptions();

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

            $this->_table_column_list[] = $column;
        }
    }

    protected function initData($column_options){
        return [array_map(function($name) use(&$new_data){
            return '';
        }, array_column($column_options, 'name', 'name'))];
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
        $html = $this->buildRows($column_data, $options);

        return $html;
    }

    protected function buildRows($column_data, $options = []){
        $options = $options ?: $this->_table_column_list;

        $html = '';

        foreach($column_data as $item_data){
            $html .= '<tr>';
            foreach ($options as $k => $column){
                $html .= "<td {$column['td_extra_attr']} class='sub_item_{$column['type']}'>{$item_data[$column['name']]}</td>";
            }
            !$this->_hide_btn && $html .= "<td> <button type='button' class='btn btn-danger btn-sm' onclick="."$(this).parents('tr').remove();".">删除</button> </td>";
            $html .= '</tr>';
        }

        return $html;
    }

    public function setColReadOnly($col_readonly){
        $this->_col_readonly = $col_readonly;

        return $this;
    }
}