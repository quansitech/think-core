<?php
namespace Behavior;

use Illuminate\Database\Capsule\Manager as CapsuleManager;
/**
 * 行为扩展：加载Eloquent
 */
class EloquentLoadBehavior {
    public function run(&$params) {
        $database_config = require LARA_DIR . '/config/database.php';

        $manager = new CapsuleManager();

        foreach ($database_config['connections'] as $connection_name => $connection_config) {
            if($database_config['default'] === $connection_name) {
                $manager->addConnection($connection_config);
                continue;
            }
            $manager->addConnection($connection_config, $connection_name);
        }

        $manager->setAsGlobal();

        $manager->bootEloquent();
    }
}