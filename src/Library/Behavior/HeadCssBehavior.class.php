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

        // <!-- qs-register:css -->
        // <!-- end-register -->
        $search_str = '<!-- qs-register:css -->';
        $content = Str::replaceFirst($search_str, $search_str . $link, $content);
    }
}