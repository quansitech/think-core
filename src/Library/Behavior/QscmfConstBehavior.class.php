<?php
namespace Behavior;

class QscmfConstBehavior{

    public function run(&$_data){
        defined('DOMAIN') or define('DOMAIN', env('DOMAIN', $_SERVER['HTTP_HOST']));
        defined('SITE_URL') or define('SITE_URL', DOMAIN . (trim( __ROOT__, DIRECTORY_SEPARATOR) == '' ? '' : DIRECTORY_SEPARATOR. trim( __ROOT__, DIRECTORY_SEPARATOR)));
        defined('HTTP_PROTOCOL') or  define('HTTP_PROTOCOL', env('HTTP_PROTOCOL', $_SERVER[C('HTTP_PROTOCOL_KEY')]));
    }
}