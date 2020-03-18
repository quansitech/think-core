<?php
namespace Larafortp;

use Bootstrap\RegisterContainer;

class ArtisanHack{

    static public function init($app){
        self::registerMigrator($app);
    }

    static private function registerMigrator($app){
        $paths = RegisterContainer::getRegisterMigratePaths();
        $app->afterResolving('migrator', function ($migrator) use ($paths) {
            foreach ((array) $paths as $path) {
                $migrator->path($path);
            }
        });
    }
}