<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\MigrationCreator;

class CmmMigrationCreator extends MigrationCreator{

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }
}