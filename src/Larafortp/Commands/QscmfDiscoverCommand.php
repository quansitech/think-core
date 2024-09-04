<?php

namespace Larafortp\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class QscmfDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qscmf:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = [];

        if ($this->files->exists($path = app()->basePath().'/vendor/composer/installed.json')) {
            $packages = json_decode($this->files->get($path), true);
        }
        if (isset($packages['packages'])){
            $packages=$packages['packages'];
        }

        $this->write(collect($packages)->mapWithKeys(function ($package) {
            return [$this->format($package['name']) => $package['extra']['qscmf'] ?? []];
        })->filter()->all());

        $this->info('Qscmf Package manifest generated successfully.');
    }

    /**
     * Format the given package name.
     *
     * @param  string  $package
     * @return string
     */
    protected function format($package)
    {
        return str_replace(app()->basePath('vendor').'/', '', $package);
    }

    /**
     * Write the given manifest array to disk.
     *
     * @param  array  $manifest
     * @return void
     *
     * @throws \Exception
     */
    protected function write(array $manifest)
    {
        $manifestPath = app()->bootstrapPath().'/cache/qscmf-packages.php';
        if (! is_writable(dirname($manifestPath))) {
            throw new Exception('The '.dirname($manifestPath).' directory must be present and writable.');
        }

        $this->files->replace(
            $manifestPath, '<?php return '.var_export($manifest, true).';'
        );
    }
}
