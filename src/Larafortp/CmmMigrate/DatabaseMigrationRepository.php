<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Migrations\DatabaseMigrationRepository as BaseMigrationRepository;

class DatabaseMigrationRepository extends BaseMigrationRepository{


    /**
     * Create a new database migration repository instance.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
     * @param  string  $table
     * @return void
     */
    public function __construct(Resolver $resolver, $table)
    {
        parent::__construct($resolver, $table);
    }

    /**
     * Create the migration repository data store.
     *
     * @return void
     */
    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            // The migrations table is responsible for keeping track of which of the
            // migrations have actually run for the application. We'll create the
            // table to hold the migration file's path as well as the batch ID.
            $table->increments('id');
            $table->string('migration');
            $table->boolean('before');
            $table->boolean('run');
            $table->boolean('after');
            $table->integer('batch');
        });
    }

    /**
     * Get the completed migrations.
     *
     * @return array
     */
    public function getRan()
    {
        return $this->table()
            ->where('before', 1)
            ->where('run', 1)
            ->where('after', 1)
            ->orderBy('batch', 'asc')
            ->orderBy('migration', 'asc')
            ->pluck('migration')->all();
    }


    public function getAllMigrations(){
        return $this->table()
                ->orderBy('batch', 'asc')
                ->orderBy('migration', 'asc')
                ->pluck('migration')->all();
    }

    public function log($name, $batch, $operation = 'run', $finish = true)
    {
        $exists = $this->table()->where('migration', $name)->count();
        if(!$exists){
            $record = ['migration' => $name, 'batch' => $batch, 'before' => 0, 'run' => 0, 'after' => 0];

            $this->table()->insert($record);
        }

        if($finish){
            $this->table()->where('migration', $name)->update(['batch' => $batch, 'before' => 1, 'run' => 1, 'after' => 1]);
        }
        else{
            $this->table()->where('migration', $name)->update([$operation => 1]);
        }

    }

    public function rollbackLog($name, $operation)
    {
        return $this->table()->where('migration', $name)->update([$operation => 0]);
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return $this->table()
            ->where('before', 1)
            ->where('run', 1)
            ->where('after', 1)
            ->max('batch');
    }

    public function ranOperation($name, $operation)
    {
        return $this->table()->where('migration', $name)->value($operation) ? true : false;
    }
}