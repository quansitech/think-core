<?php
namespace Behavior;

use Illuminate\Support\Str;

class InjectHeadBehavior{

    use InjectTrait;

    public function run(&$params){
        $template_suffix = $params['template_suffix'];
        $extend_name = $params['extend_name'];

        $this->inject_js($params['content'], $extend_name, $template_suffix);

        $this->inject_css($params['content'], $extend_name, $template_suffix);
    }

    private function inject_js(&$content, $extend_name, $template_suffix){
        $tag_begin = C('QS_REGISTER_JS_TAG_BEGIN');
        $tag_end = C('QS_REGISTER_JS_TAG_END');
        $inject_sign_html = $tag_begin .PHP_EOL. $tag_end;

        $can_inject = $this->canInject($extend_name, $content, $template_suffix, $tag_begin);
        $content = $can_inject ? str_ireplace('</head>',$inject_sign_html. PHP_EOL .'</head>',$content) : $content;
    }

    private function inject_css(&$content, $extend_name, $template_suffix){
        $tag_begin = C('QS_REGISTER_CSS_TAG_BEGIN');
        $tag_end = C('QS_REGISTER_CSS_TAG_END');
        $inject_sign_html = $tag_begin .PHP_EOL. $tag_end;

        $can_inject = $this->canInject($extend_name, $content, $template_suffix, $tag_begin);
        $content = $can_inject ? Str::replaceFirst('<script', $inject_sign_html . PHP_EOL .'<script', $content) : $content;
    }
}