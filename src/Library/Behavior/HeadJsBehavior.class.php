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

        // <!-- qs-register:js -->
        // <!-- end-register -->
        $search_str = '<!-- qs-register:js -->';
        $content = str_ireplace($search_str,$search_str . $scripts, $content);
    }
}