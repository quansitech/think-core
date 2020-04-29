<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Database\Console\Migrations\RefreshCommand;
use Symfony\Component\Console\Input\InputOption;

class CmmRefreshCommand extends RefreshCommand{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        // Next we'll gather some of the options so that we can have the right options
        // to pass to the commands. This includes options such as which database to
        // use and the path to use for the migration. Then we'll run the command.
        $database = $this->input->getOption('database');

        $path = $this->input->getOption('path');

        // If the "step" option is specified it means we only want to rollback a small
        // number of migrations before migrating again. For example, the user might
        // only rollback and remigrate the latest four migrations instead of all.
        $step = $this->input->getOption('step') ?: 0;

        $no_cmd = $this->input->getOption('no-cmd') ?: false;

        if ($step > 0) {
            $this->runRollback($database, $path, $step, $no_cmd);
        } else {
            $this->runReset($database, $path, $no_cmd);
        }

        // The refresh command is essentially just a brief aggregate of a few other of
        // the migration commands and just provides a convenient wrapper to execute
        // them in succession. We'll also see if we need to re-seed the database.
        $this->call('migrate', array_filter([
            '--database' => $database,
            '--path' => $path,
            '--realpath' => $this->input->getOption('realpath'),
            '--force' => true,
            '--no-cmd' => $no_cmd
        ]));

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }
    }

    protected function runRollback($database, $path, $step, $no_cmd = false)
    {
        $this->call('migrate:rollback', array_filter([
            '--database' => $database,
            '--path' => $path,
            '--realpath' => $this->input->getOption('realpath'),
            '--step' => $step,
            '--force' => true,
            '--no-cmd' => $no_cmd
        ]));
    }


    protected function runReset($database, $path, $no_cmd = false)
    {
        $this->call('migrate:reset', array_filter([
            '--database' => $database,
            '--path' => $path,
            '--realpath' => $this->input->getOption('realpath'),
            '--force' => true,
            '--no-cmd' => $no_cmd
        ]));
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],

            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to be executed'],

            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],

            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],

            ['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],

            ['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted & re-run'],

            ['no-cmd', null, InputOption::VALUE_NONE, 'dont run command']
        ];
    }
}