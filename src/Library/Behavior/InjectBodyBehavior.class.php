<?php
namespace Behavior;

class InjectBodyBehavior{

    public function run(&$params){
        $template_suffix = $params['template_suffix'];
        $extend_name = $params['extend_name'];
        $this->inject_body($params['content'], $extend_name, $template_suffix);
    }

    private function can_inject($extend_name, $content, $template_suffix, $sign_str){
        $layout_path = T('Admin@default/common/dashboard_layout');

        if(false === strpos($extend_name, $template_suffix)) {
            // 解析规则为 模块@主题/控制器/操作
            $extend_name = T($extend_name);
        }

        if (DIRECTORY_SEPARATOR.normalizeRelativePath($extend_name) !== $layout_path){
            return false;
        }

        if (strpos($content, $sign_str)){
            return false;
        }

        return true;
    }

    private function inject_body(&$content, $extend_name, $template_suffix){
        $tag_begin = C('QS_REGISTER_BODY_TAG_BEGIN');
        $tag_end = C('QS_REGISTER_BODY_TAG_END');
        $inject_sign_html = $tag_begin .PHP_EOL. $tag_end;

        $can_inject = $this->can_inject($extend_name, $content, $template_suffix, $tag_begin);
        $content = $can_inject ? str_ireplace('</body>',$inject_sign_html. PHP_EOL .'</body>',$content) : $content;
    }
}