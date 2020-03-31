<?php
namespace Behavior;

class QscmfConstBehavior{

    public function run(&$_data){
        $root = trim( __ROOT__, DIRECTORY_SEPARATOR) == '' ? '' : DIRECTORY_SEPARATOR. trim( __ROOT__, DIRECTORY_SEPARATOR);
        defined('DOMAIN') or define('DOMAIN', env('DOMAIN', $_SERVER['HTTP_HOST']));
        defined('SITE_URL') or define('SITE_URL', DOMAIN . $root);
        defined('HTTP_PROTOCOL') or  define('HTTP_PROTOCOL', env('HTTP_PROTOCOL', $_SERVER[C('HTTP_PROTOCOL_KEY')]));
        defined('REQUEST_URI') or define('REQUEST_URI', $root . $_SERVER['REQUEST_URI']);
    }
}