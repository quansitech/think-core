<?php
namespace Behavior;

use Bootstrap\RegisterContainer;

class BodyHtmlBehavior{

    public function run(&$content){

        $body_html = RegisterContainer::getBodyHtml();
        if ($body_html){
            $html = implode(PHP_EOL, $body_html);

            $search_str = C('QS_REGISTER_BODY_TAG_END');
            $content = str_ireplace($search_str, $html .PHP_EOL. $search_str, $content);
        }
    }
}