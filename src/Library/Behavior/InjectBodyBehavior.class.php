<?php
namespace Behavior;

class InjectBodyBehavior{

    use InjectTrait;

    public function run(&$params){
        $template_suffix = $params['template_suffix'];
        $extend_name = $params['extend_name'];
        $this->inject_body($params['content'], $extend_name, $template_suffix);
    }

    private function inject_body(&$content, $extend_name, $template_suffix){
        $tag_begin = C('QS_REGISTER_BODY_TAG_BEGIN');
        $tag_end = C('QS_REGISTER_BODY_TAG_END');
        $inject_sign_html = $tag_begin .PHP_EOL. $tag_end;

        $can_inject = $this->canInject($extend_name, $content, $template_suffix, $tag_begin);
        $content = $can_inject ? str_ireplace('</body>',$inject_sign_html. PHP_EOL .'</body>',$content) : $content;
    }
}