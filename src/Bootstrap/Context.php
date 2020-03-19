<?php
namespace Bootstrap;

class Context{

    const PACKAGE_CACHE_FILE = 'qscmf-packages.php';

    static public function providerRegister($is_lara = false){
        $cache_file = LARA_DIR . '/bootstrap/cache/' . self::PACKAGE_CACHE_FILE;
        if(file_exists($cache_file)){
            $packages = require $cache_file;
            collect($packages)->values()->each(function($item, $key) use ($is_lara){
                collect($item['providers'])->each(function($cls, $index) use ($is_lara){
                    if(class_exists($cls)){
                        $is_lara ? (new $cls())->registerLara() : (new $cls())->register();
                    }
                });
            });
        }
    }
}