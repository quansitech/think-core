<?php
namespace Larafortp\CmmMigrate;

use Illuminate\Database\Migrations\MigrationCreator;

class CmmMigrationCreator extends MigrationCreator{
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