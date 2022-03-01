<?php

namespace Qscmf\Builder;

trait TSubBuilder
{
    public function genQsSubBuilderRowToJs($has_column = 1){
        if(IS_POST){
            $columns = I('post.columns');
            $this->transcodeColumns($columns);

            $has_column && !defined('QS_SUB_RENEW_ROW') && define('QS_SUB_RENEW_ROW', 1);
            $this->ajaxReturn(['data' => (new SubTableBuilder())->genNewRowHtml($columns)]);
        }
    }

    protected function transcodeColumns(&$columns){
        $columns = array_map(function($column){
            $column['editable'] = $this->transcodeEditable($column['editable']);
            return $column;
        }, $columns);
    }

    protected function transcodeEditable($editable){
        return $editable === 'false' ? false : $editable;
    }

}