<?php
namespace Behavior;

class InjectHeadJsBehavior{

    public function run(&$content){
        $inject_sign_html = '<!-- qs-register:js -->'.PHP_EOL.'<!-- end-register -->';
        $find = strpos($content, '<!-- qs-register:js -->');

        $content = !$find ? str_ireplace('</head>',$inject_sign_html.'</head>',$content) : $content;
    }
}