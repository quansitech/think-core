<?php
namespace Behavior;

use Bootstrap\RegisterContainer;

class HeaderNavbarRightHtmlBehavior{

    public function run(&$content){

        $body_html = RegisterContainer::getHeaderNavbarRightHtml();
        if ($body_html){
            $html = implode(PHP_EOL, $body_html);

            $search_str = '<ul class="nav navbar-nav">';

            $content = str_ireplace($search_str, $search_str.PHP_EOL.$html, $content);
        }
    }
}