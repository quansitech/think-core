<?php
namespace Behavior;

use Bootstrap\RegisterContainer;

class HeadJsBehavior{

    public function run(&$content){

        $js_srcs = RegisterContainer::getHeadJs();
        $scripts = '';
        foreach($js_srcs as $src){
            $async = $src['async'] ? 'async' : '';
            $scripts .= "<script $async src='{$src["src"]}'></script>";
        }

        $search_str = C('QS_REGISTER_JS_TAG_END');
        $content = str_ireplace($search_str, $scripts .PHP_EOL. $search_str, $content);
    }
}