<?php


namespace Behavior;


trait InjectTrait
{

    public function canInject($extend_name, $content, $template_suffix, $sign_str){
        if(false === strpos($extend_name, $template_suffix)) {
            // 解析规则为 模块@主题/控制器/操作
            $extend_name = T($extend_name);
        }

        if (!$this->_existsLayout($extend_name)){
            return false;
        }

        if (strpos($content, $sign_str)){
            return false;
        }

        return true;
    }

    private function _getLayoutPath():array{
        $custom_layout = C("QS_INJECT_LAYOUT_PATH");
        $custom_layout = is_array($custom_layout) ? $custom_layout : explode(",", $custom_layout);

        $def_layout = T('Admin@default/common/dashboard_layout');

        return array_merge([$def_layout],$custom_layout);
    }

    private function _existsLayout($extend_name):bool{
        $list = $this->_getLayoutPath();
        $extend_name = normalizeRelativePath($extend_name);

        return in_array($extend_name, $list, true);
    }
}