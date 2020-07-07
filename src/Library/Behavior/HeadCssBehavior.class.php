<?php
namespace Behavior;

use Bootstrap\RegisterContainer;
use Illuminate\Support\Str;

class HeadCssBehavior{

    public function run(&$content){

        $css_hrefs = RegisterContainer::getHeadCss();
        $link = '';
        foreach($css_hrefs as $href){
            $link .= "<link href='{$href["href"]}' rel='stylesheet' type='text/css'>";
        }
        $content = Str::replaceFirst('<script', $link . '<script', $content);
    }
}