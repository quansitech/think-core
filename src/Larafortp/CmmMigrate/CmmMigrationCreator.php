<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\MigrationCreator;

class CmmMigrationCreator extends MigrationCreator{

    public function __construct(Filesystem $files, $customStubPath)
    {
        parent::__construct($files, $customStubPath);
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