<?php
namespace Qscmf\Core;

use Illuminate\Filesystem\Filesystem;

class Context{

    const PACKAGE_CACHE_FILE = 'qscmf-packages.php';

    static public function providerRegister(){
        $cache_file = LARA_DIR . '/bootstrap/cache/' . self::PACKAGE_CACHE_FILE;
        if(file_exists($cache_file)){
            $packages = require $cache_file;
            collect($packages)->values()->each(function($item, $key){
                collect($item['providers'])->each(function($cls, $index){
                    if(class_exists($cls)){
                        (new $cls())->register();
                    }
                });
            });
        }
    }
}