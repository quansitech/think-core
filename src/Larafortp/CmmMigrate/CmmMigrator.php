<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class CmmMigrator extends Migrator{

    public function __construct(MigrationRepositoryInterface $repository, Resolver $resolver, Filesystem $files, Dispatcher $dispatcher = null)
    {
        parent::__construct($repository, $resolver, $files, $dispatcher);
    }

    /**
     * Run an array of migrations.
     *
     * @param  array  $migrations
     * @param  array  $options
     * @return void
     */
    public function runPending(array $migrations, array $options = [])
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($migrations) === 0) {
            $this->fireMigrationEvent(new NoPendingMigrations('up'));

            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution. We will also extract a few of the options.
        $batch = $this->repository->getNextBatchNumber();

        $pretend = $options['pretend'] ?? false;

        $step = $options['step'] ?? false;

        $no_cmd = $options['no-cmd'] ?? false;

        $this->fireMigrationEvent(new MigrationsStarted('up'));

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $file) {
            $this->runUp($file, $batch, $pretend, $no_cmd);

            if ($step) {
                $batch++;
            }
        }

        $this->fireMigrationEvent(new MigrationsEnded('up'));
    }

    protected function runUp($file, $batch, $pretend, $no_cmd = false)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        if(!$no_cmd && method_exists($migration, 'beforeCmmUp') && !$this->repository->ranOperation($name, 'before'))
        {
            $this->runCommon($name, $migration, 'beforeCmmUp', $pretend, false);
            $this->repository->log($name, $batch, 'before', false);
        }

        if(!$this->repository->ranOperation($name, 'run')){
            $this->runCommon($name, $migration, 'up', $pretend);
            $this->repository->log($name, $batch, 'run', false);
        }

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.

        if(!$no_cmd && method_exists($migration, 'afterCmmUp') && !$this->repository->ranOperation($name, 'after'))
        {
            $this->runCommon($name, $migration, 'afterCmmUp', $pretend, false);
            $this->repository->log($name, $batch, 'after', false);
        }

        $this->repository->log($name, $batch);

    }

    protected function runCommon($name, $migration, $method, $pretend, $event = true){
        if ($pretend) {
            return $this->pretendToRun($migration, $method);
        }

        if(strpos(strtolower($method), 'up') !== false){
            $this->note("<comment>Migrating $method:</comment> {$name}");
        }
        else{
            $this->note("<comment>Rolling back $method:</comment> {$name}");
        }

        $startTime = microtime(true);

        $this->runMigration($migration, $method, $event);

        $runTime = round(microtime(true) - $startTime, 2);

        if(strpos(strtolower($method), 'up') !== false){
            $this->note("<info>Migrated $method:</info>  {$name} ({$runTime} seconds)");
        }
        else{
            $this->note("<info>Rolled back $method:</info>  {$name} ({$runTime} seconds)");
        }


    }

    protected function runMigration($migration, $method, $event = true)
    {
        $connection = $this->resolveConnection(
            $migration->getConnection()
        );

        $callback = function () use ($connection, $migration, $method, $event) {
            if (method_exists($migration, $method)) {
                $event && $this->fireMigrationEvent(new MigrationStarted($migration, $method));

                $this->runMethod($connection, $migration, $method);

                $event && $this->fireMigrationEvent(new MigrationEnded($migration, $method));
            }
        };

        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
        && $migration->withinTransaction
            ? $connection->transaction($callback)
            : $callback();
    }

    /**
     * Rollback the given migrations.
     *
     * @param  array  $migrations
     * @param  array|string  $paths
     * @param  array  $options
     * @return array
     */
    protected function rollbackMigrations(array $migrations, $paths, array $options)
    {
        $rolledBack = [];

        $this->requireFiles($files = $this->getMigrationFiles($paths));

        $this->fireMigrationEvent(new MigrationsStarted('down'));

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order. This getLast method on the
        // repository already returns these migration's names in reverse order.
        foreach ($migrations as $migration) {
            $migration = (object) $migration;

            if (! $file = Arr::get($files, $migration->migration)) {
                $this->note("<fg=red>Migration not found:</> {$migration->migration}");

                continue;
            }

            $rolledBack[] = $file;

            $this->runDown(
                $file, $migration,
                $options['pretend'] ?? false, $options['no-cmd'] ?? false
            );
        }

        $this->fireMigrationEvent(new MigrationsEnded('down'));

        return $rolledBack;
    }

    protected function runDown($file, $migration, $pretend, $no_cmd = false)
    {
        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
        $instance = $this->resolve(
            $name = $this->getMigrationName($file)
        );

        if(!$no_cmd && method_exists($instance, 'afterCmmDown') && $this->repository->ranOperation($name, 'after'))
        {
            $this->runCommon($name, $instance, 'afterCmmDown', $pretend, false);
            $this->repository->rollbackLog($name, 'after');
        }

        if($this->repository->ranOperation($name, 'run')){
            $this->runCommon($name, $instance, 'down', $pretend);
            $this->repository->rollbackLog($name, 'run');
        }


        if(!$no_cmd && method_exists($instance, 'beforeCmmDown') && $this->repository->ranOperation($name, 'before'))
        {
            $this->runCommon($name, $instance, 'beforeCmmDown', $pretend, false);
            $this->repository->rollbackLog($name, 'before');
        }

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($migration);

    }

    public function reset($paths = [], $pretend = false, $no_cmd = false)
    {
        // Next, we will reverse the migration list so we can run them back in the
        // correct order for resetting this database. This will allow us to get
        // the database back into its "empty" state ready for the migrations.
        $migrations = array_reverse($this->repository->getAllMigrations());

        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');

            return [];
        }

        return $this->resetMigrations($migrations, $paths, $pretend, $no_cmd);
    }

    protected function resetMigrations(array $migrations, array $paths, $pretend = false, $no_cmd = false)
    {
        // Since the getRan method that retrieves the migration name just gives us the
        // migration name, we will format the names into objects with the name as a
        // property on the objects so that we can pass it to the rollback method.
        $migrations = collect($migrations)->map(function ($m) {
            return (object) ['migration' => $m];
        })->all();

        return $this->rollbackMigrations(
            $migrations, $paths, compact('pretend', 'no_cmd')
        );
    }
}