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

        $search_str = C('QS_REGISTER_CSS_TAG_END');
        $content = Str::replaceFirst($search_str, $link .PHP_EOL. $search_str, $content);
    }
}