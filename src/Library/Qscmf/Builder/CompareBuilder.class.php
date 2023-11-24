<?php
/**
 * Created by PhpStorm.
 * User: 95869
 * Date: 2018/11/6
 * Time: 11:53
 */

namespace Qscmf\Builder;


class CompareBuilder extends BaseBuilder
{
    const ITEM_TYPE_TEXT='text';
    const ITEM_TYPE_SELECT='select';
    const ITEM_TYPE_DATE='date';
    const ITEM_TYPE_DATETIME='datetime';
    const ITEM_TYPE_PICTURE='picture';
    const ITEM_TYPE_PICTURES='pictures';
    const ITEM_TYPE_UEDITOR='ueditor';
    const ITEM_TYPE_HTMLDIFF='htmldiff';

    private $_compare_items=[];
    private $_old_data=[];
    private $_new_data=[];

    /**
     * @param array $old_data
     * @param array $new_data
     * @return CompareBuilder
     */
    public function setData($old_data,$new_data)
    {
        $this->_old_data = $old_data;
        $this->_new_data = array_merge($old_data,$new_data);
       return $this;
    }

    /**
     * 初始化方法
     */
    protected function _initialize() {
        $module_name = 'Admin';
        $this->_template = __DIR__ .'/Layout/'.$module_name.'/compare.html';
    }

    public function addCompareItem($name,$type,$title,$option=[]){
        $this->appendColumnName($name);
        $item=[
            'name'=>$name,
            'type'=>$type,
            'title'=>$title,
            'option'=>$option
        ];

        $this->_compare_items[]=$item;
        return $this;
    }

    /**
     * @deprecated 已在v13版本删除， 请使用 build 代替
     * 显示页面
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        E("display method is delete,use build instead");
    }

    public function build(){
        $this->assign('nid', $this->_nid);
        $this->assign('extra_html', $this->_extra_html);
        $this->assign('old_data', $this->_old_data);
        $this->assign('new_data', $this->_new_data);
        $this->assign('compare_items', $this->_compare_items);
        $this->assign('meta_title', $this->_meta_title);
        $this->assign('top_html', $this->_top_html);            // 顶部自定义html代码
        $this->assign('compare_builder_path', __DIR__ . '/compareBuilder.html');
        parent::display($this->_template);
    }
}