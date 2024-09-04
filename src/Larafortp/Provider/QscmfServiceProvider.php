<?php
namespace Larafortp\Provider;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
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
use Larafortp\Doctrine\Types\TinyInteger;

class QscmfServiceProvider extends ServiceProvider
{
    protected $commands = [
        QscmfDiscoverCommand::class
    ];

    public function register(){
        $this->commands($this->commands);

        if(env("DB_CONNECTION")){
            DB::registerDoctrineType(TinyInteger::class, TinyInteger::NAME, 'TINYINT');
        }

        $this->app->extend('migrator', function ($object, $app) {
            $repository = $app['migration.repository'];

            return new CmmMigrator($repository, $app['db'], $app['files'], $app['events']);
        });

        $this->app->extend('command.migrate', function ($object, $app) {
            return new CmmMigrateCommand($app['migrator'], $app[Dispatcher::class]);
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
            return new CmmMigrationCreator($app['files'], $app->basePath('stubs'));
        });

        $this->app->extend('migration.repository', function($object, $app){
            $table = $app['config']['database.migrations'];

            return  new DatabaseMigrationRepository($app['db'], $table);
        });
    }
}