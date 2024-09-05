<?php
namespace Larafortp\Provider;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Support\ServiceProvider;
use Larafortp\CmmMigrate\CmmFreshCommand;
use Larafortp\CmmMigrate\CmmMigrateCommand;
use Larafortp\CmmMigrate\CmmMigrationCreator;
use Larafortp\CmmMigrate\CmmMigrator;
use Larafortp\CmmMigrate\CmmRefreshCommand;
use Larafortp\CmmMigrate\CmmResetCommand;
use Larafortp\CmmMigrate\CmmRollbackCommand;
use Larafortp\CmmMigrate\DatabaseMigrationRepository;
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

        $this->app->extend(MigrateCommand::class, function ($object, $app) {
            return new CmmMigrateCommand($app['migrator'], $app[Dispatcher::class]);
        });

        $this->app->extend(FreshCommand::class, function ($object, $app) {
            return new CmmFreshCommand($app['migrator']);
        });

        $this->app->extend(RefreshCommand::class, function ($object, $app) {
            return new CmmRefreshCommand();
        });

        $this->app->extend(ResetCommand::class, function ($object, $app) {
            return new CmmResetCommand($app['migrator']);
        });

        $this->app->extend(RollbackCommand::class, function ($object, $app) {
            return new CmmRollbackCommand($app['migrator']);
        });

        $this->app->extend('migration.creator', function ($object, $app) {
            return new CmmMigrationCreator($app['files'], $app->basePath('stubs'));
        });

        $this->app->extend('migration.repository', function($object, $app){
            $table = $app['config']['database.migrations'];

            return  new DatabaseMigrationRepository($app['db'], $table);
        });
    }
}