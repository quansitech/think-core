<?php

namespace Qscmf\Builder;

trait TSubBuilder
{
    public function genQsSubBuilderRowToJs($has_column = 1){
        if(IS_POST){
            $index = I("get.index");
            $need_validate = I("get.need_validate") === '1';
            $columns = I('post.columns');
            $this->transcodeColumns($columns);

            $has_column && !defined('QS_SUB_RENEW_ROW') && define('QS_SUB_RENEW_ROW', 1);
            $this->ajaxReturn(['data' => (new SubTableBuilder())
                ->setNeedValidate($need_validate)
                ->genNewRowHtml($columns, $this->genIndex($index))
            ]);
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
    protected function genIndex($index){
        return $index;
    }

}