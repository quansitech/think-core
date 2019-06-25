<?php

namespace Larafortp\Tests\Features;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Larafortp\Tests\TestCase;

class ArtisanTest extends TestCase
{
    public function testMakeModel()
    {
        $this->artisan('make:model Test');

        $this->assertFileExists(base_path('app/Test.php'));
    }

    public function testMakeFactory()
    {
        $this->artisan('make:factory Test');

        $this->assertFileExists(database_path('factories/Test.php'));
    }

    public function testMakeSeed()
    {
        $this->artisan('make:seeder TestSeeder');

        $this->assertFileExists(database_path('seeds/TestSeeder.php'));
    }

    public function testMakeMigration()
    {
        $this->artisan('make:migration create_test_table');

        $files = new Filesystem();
        $exists_flag = false;
        collect($files->files(database_path('migrations')))->each(function ($item) use (&$exists_flag) {
            if (Str::is(date('Y_m_d_Hi').'*'.'_create_test_table.php', $item->getRelativePathname())) {
                $exists_flag = true;
            }
        });
        $this->assertTrue($exists_flag);
    }
}
