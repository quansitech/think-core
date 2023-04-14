<?php


namespace Behavior;


trait InjectTrait
{

    public function canInject($extend_name, $content, $template_suffix, $sign_str){
        $layout_path = T('Admin@default/common/dashboard_layout');

        if(false === strpos($extend_name, $template_suffix)) {
            // 解析规则为 模块@主题/控制器/操作
            $extend_name = T($extend_name);
        }

        if (normalizeRelativePath($extend_name) !== $layout_path){
            return false;
        }

        if (strpos($content, $sign_str)){
            return false;
        }

        return true;
    }
}