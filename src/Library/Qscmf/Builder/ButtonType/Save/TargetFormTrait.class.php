<?php


namespace Qscmf\Builder\ButtonType\Save;

trait TargetFormTrait
{
    /**
     * @deprecated 在v13会对该方法进行重构，原因是SubTableBuilder也可能会采用该方法来设置 column 的class，当同时作为listBuilder的modal使用时，就会被错误的一并save提交
     * 先采用全局变量来开发重置能力，但全局变量容易存在冲突，并不是一种好的解决方案，仅作过渡使用
     * 合理的做法应该让ListBuilder或者SubTableBuilder来决定Column 的TargetForm，避免互相影响。
     * 显示页面
     */
    public function getSaveTargetForm(){
        return Save::$target_form;
    }

}