<?php

namespace Testing;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract  class TestCase extends BaseTestCase {

    use InteractsWithConsole;
    use MakesHttpRequests;
    use InteractsWithDatabase;
    use InteractsWithTpConsole;

    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;


    abstract public function laraPath():string;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require $this->laraPath() . '/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (!$this->app) {
            $this->app = $this->createApplication();
        }

        $this->uninstall();
        $this->artisan('migrate:refresh');

        $this->loadTpConfig();

        Facade::clearResolvedInstances();

        $this->setUpHasRun = true;
    }

    protected function uninstall()
    {
        $tables = DB::select("SELECT CONCAT('',table_name) as tb, table_type as table_type FROM information_schema.`TABLES` WHERE table_schema='" . env('DB_DATABASE' ) . "'");

        foreach($tables as $table){
            switch($table->table_type){
                case 'BASE TABLE':
                    DB::statement('drop table ' . $table->tb);
                    break;
                case 'VIEW':
                    DB::statement('drop view ' . $table->tb);
                    break;
            }
        }

        $procedures = DB::select("show procedure status where Db='" . env('DB_DATABASE' ) . "'");
        foreach($procedures as $procedure){
            DB::unprepared('drop procedure ' . $procedure->Name);
        }

        $events = DB::select("SELECT * FROM information_schema.EVENTS where event_schema='" . env('DB_DATABASE' ) . "'");
        foreach($events as $event){
            DB::unprepared('drop event ' . $event->EVENT_NAME);
        }
    }

    protected function loadTpConfig(){
        require __DIR__ . '/../ConstDefine.php';
        C(load_config( __DIR__ . '/../Library/Qscmf/Conf/config.php'));
        C(load_config( $this->laraPath() . '/../app/Common/Conf/config.php'));

        spl_autoload_register(function($class){
            $name           =   strstr($class, '\\', true);
            $lib_path = base_path('../vendor/tiderjian/think-core/src/Library/');
            if(is_dir($lib_path.$name)){
                // Library目录下面的命名空间自动定位
                $path       =   $lib_path;
            }else{
                $path       =   base_path('../app/');
            }
            $ext = '.class.php';
            $filename       =   $path . str_replace('\\', '/', $class) . $ext;
            if(is_file($filename)) {
                // Win环境下面严格区分大小写
                $is_win = strstr(PHP_OS, 'WIN') ? 1 : 0;
                if ($is_win && false === strpos(str_replace('/', '\\', realpath($filename)), $class . $ext)){
                    return ;
                }
                include $filename;
            }
        });
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            $this->app->flush();

            $this->app = null;
        }

        $this->setUpHasRun = false;

        if (class_exists(Carbon::class)) {
            Carbon::setTestNow();
        }

        if (class_exists(CarbonImmutable::class)) {
            CarbonImmutable::setTestNow();
        }

        Artisan::forgetBootstrappers();
    }

    /**
     * Register a callback to be run before the application is destroyed.
     *
     * @param  callable  $callback
     * @return void
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }
}
