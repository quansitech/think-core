<?php
namespace Behavior;

use Illuminate\Support\Str;

class InjectHeadCssBehavior{

    public function run(&$content){
        $inject_sign_html = '<!-- qs-register:css -->'.PHP_EOL.'<!-- end-register -->';
        $find = strpos($content, '<!-- qs-register:css -->');

        $content = !$find ? Str::replaceFirst('<script', $inject_sign_html . '<script', $content) : $content;
    }
}