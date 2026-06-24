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
    use DBTrait;

    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;


    abstract public function laraPath():string;

    /**
     * 获取项目根目录路径（即包含 tp.php、composer.json 的目录）。
     *
     * 通过 laraPath()（lara/ 目录）的上一级推导，而非依赖 think-core 在 vendor
     * 中的相对层级回溯。因为 think-core 通过 path 仓库 symlink 安装时，__DIR__
     * 会被解析到符号链接的真实物理路径，导致原先的 __DIR__/../../../../../ 回溯
     * 无法回到宿主项目根目录。
     *
     * @return string
     */
    public function projectPath(): string
    {
        return dirname(realpath($this->laraPath()));
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require $this->laraPath() . '/bootstrap/app.php';

        \Bootstrap\Context::providerRegister(true);
        \Larafortp\ArtisanHack::init($app);

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
        $this->install();

        $this->loadTpConfig();

        Facade::clearResolvedInstances();

        $this->setUpHasRun = true;
    }

    protected function loadTpConfig(){
        // think-core 通过 path 仓库 symlink 安装时，ConstDefine.php 内的
        // realpath(__DIR__ . '/../../../..') 会解析到符号链接真实物理路径（think-core
        // 仓库根），而非宿主项目根，导致 ROOT_PATH 等常量错误。这里基于 projectPath()
        // 预先定义正确的 ROOT_PATH，ConstDefine.php 的 defined() || define() 守卫会沿用之。
        defined('ROOT_PATH') || define('ROOT_PATH', $this->projectPath());

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
