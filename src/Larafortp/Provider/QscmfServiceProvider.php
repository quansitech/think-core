<?php
namespace Larafortp\Provider;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;
use Larafortp\CmmMigrate\CmmFreshCommand;
use Larafortp\CmmMigrate\CmmMigrateCommand;
use Larafortp\CmmMigrate\CmmMigrationCreator;
use Larafortp\CmmMigrate\CmmMigrator;
use Larafortp\CmmMigrate\CmmRefreshCommand;
use Larafortp\CmmMigrate\CmmResetCommand;
use Larafortp\CmmMigrate\CmmRollbackCommand;
use Larafortp\Commands\QscmfCreateSymlinkCommand;
use Larafortp\Commands\QscmfDiscoverCommand;

class QscmfServiceProvider extends ServiceProvider
{
    protected $commands = [
        QscmfDiscoverCommand::class
    ];

    public function register(){
        $this->commands($this->commands);

        $this->app->extend('migrator', function ($object, $app) {
            $repository = $app['migration.repository'];

            return new CmmMigrator($repository, $app['db'], $app['files'], $app['events']);
        });

        $this->app->extend('command.migrate', function ($object, $app) {
            return new CmmMigrateCommand($app['migrator']);
        });

        $this->app->extend('command.migrate.fresh', function ($object, $app) {
            return new CmmFreshCommand();
        });

        $this->app->extend('command.migrate.refresh', function ($object, $app) {
            return new CmmRefreshCommand();
        });

        $this->app->extend('command.migrate.reset', function ($object, $app) {
            return new CmmResetCommand($app['migrator']);
        });

        $this->app->extend('command.migrate.rollback', function ($object, $app) {
            return new CmmRollbackCommand($app['migrator']);
        });

        $this->app->extend('migration.creator', function ($object, $app) {
            return new CmmMigrationCreator($app['files']);
        });
    }
}