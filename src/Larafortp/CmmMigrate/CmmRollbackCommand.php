<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Database\Console\Migrations\RollbackCommand;
use Symfony\Component\Console\Input\InputOption;

class CmmRollbackCommand extends RollbackCommand
{

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->migrator->usingConnection($this->option('database'), function () {
            $this->migrator->setOutput($this->output)->rollback(
                $this->getMigrationPaths(), [
                    'pretend' => $this->option('pretend'),
                    'step' => (int) $this->option('step'),
                    'no-cmd' => $this->option('no-cmd')
                ]
            );
        });

        return 0;
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

            ['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],

            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],

            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],

            ['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted'],

            ['no-cmd', null, InputOption::VALUE_NONE, 'dont run command'],
        ];
    }
}