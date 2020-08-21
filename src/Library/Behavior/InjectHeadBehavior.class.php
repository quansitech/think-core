<?php
namespace Behavior;

use Illuminate\Support\Str;

class InjectHeadBehavior{

    public function run(&$params){
        $template_suffix = $params['template_suffix'];
        $extend_name = $params['extend_name'];

        $this->inject_js($params['content'], $extend_name, $template_suffix);

        $this->inject_css($params['content'], $extend_name, $template_suffix);
    }

    private function can_inject($extend_name, $content, $template_suffix, $sign_str){
        $layout_path = T('Admin@default/common/dashboard_layout');

        if(false === strpos($extend_name, $template_suffix)) {
            // 解析规则为 模块@主题/控制器/操作
            $extend_name   =   T($extend_name);
        }

        if (DIRECTORY_SEPARATOR.normalizeRelativePath($extend_name) !== $layout_path){
            return false;
        }

        if (strpos($content, $sign_str)){
            return false;
        }

        return true;
    }

    private function inject_js(&$content, $extend_name, $template_suffix){
        $inject_sign_html = '<!-- qs-register:js -->'.PHP_EOL.'<!-- end-register -->';
        $sign_str = explode(PHP_EOL, $inject_sign_html)[0];

        $can_inject = $this->can_inject($extend_name, $content, $template_suffix, $sign_str);
        $content = $can_inject ? str_ireplace('</head>',$inject_sign_html. PHP_EOL .'</head>',$content) : $content;
    }

    private function inject_css(&$content, $extend_name, $template_suffix){
        $inject_sign_html = '<!-- qs-register:css -->'.PHP_EOL.'<!-- end-register -->';
        $sign_str = explode(PHP_EOL, $inject_sign_html)[0];

        $can_inject = $this->can_inject($extend_name, $content, $template_suffix, $sign_str);
        $content = $can_inject ? Str::replaceFirst('<script', $inject_sign_html . PHP_EOL .'<script', $content) : $content;
    }
}