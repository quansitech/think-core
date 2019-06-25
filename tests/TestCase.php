<?php

namespace Larafortp\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use InteractsWithDatabase;
    use InteractsWithContainer;
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require __DIR__.'/../stub/lara/bootstrap/app.php';
        $app->make(kernel::class)->bootstrap();

        return $app;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->install();
    }

    public function tearDown(): void
    {
        $this->uninstall();
        parent::tearDown();
    }

    protected function install()
    {
        $files = new Filesystem();

        $files->copy(__DIR__.'/Stuff/.env', __DIR__.'/../stub/lara/.env');
        $files->copyDirectory(__DIR__.'/Stuff/migrations', __DIR__.'/../stub/lara/database/migrations');

        $this->artisan('migrate');
    }

    protected function uninstall()
    {
        $files = new Filesystem();

        $files->delete(__DIR__.'/../stub/lara/.env');
        $files->cleanDirectory(__DIR__.'/../stub/lara/database/migrations');
        $files->cleanDirectory(__DIR__.'/../stub/lara/app');
        $files->cleanDirectory(__DIR__.'/../stub/lara/database/factories');
        collect($files->files(__DIR__.'/../stub/lara/database/seeds'))->each(function ($item) use ($files) {
            if ($item->getRelativePathname() != 'DatabaseSeeder.php') {
                $files->delete($item->getPathName());
            }
        });
    }
}
