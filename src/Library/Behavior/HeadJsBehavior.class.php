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
        $content = str_ireplace('</head>',$scripts.'</head>',$content);
    }
}