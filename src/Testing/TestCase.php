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

        $this->bootEloquentCapsule();

        Facade::clearResolvedInstances();

        $this->setUpHasRun = true;
    }

    /**
     * 初始化 Eloquent Capsule 与 Validator。
     *
     * 生产环境的 Capsule 由 Behavior\EloquentLoadBehavior 在 ThinkPHP 的 app_init 阶段
     * 初始化，但测试父进程走的是 Laravel Application bootstrap，不触发 ThinkPHP 流程，
     * 导致 Capsule::$instance 恒为 null —— 测试方法中 Capsule::table() 会抛
     * "Call to a member function connection() on null"。此处补齐 Capsule 与 Validator
     * 的初始化，复用同一个 database 配置（即测试库）。每个测试方法重新调用，靠 $instance
     * 守卫保持幂等。
     *
     * 注意：不复用 EloquentLoadBehavior::run()，因为其 initializeFacades() 会用一个仅含
     * events 的空 Container 替换全局 Facade application，破坏 Laravel app 已建立的 DB
     * 等 Facade 绑定（导致 "Target class [db] does not exist"）。这里保留 Laravel app 的
     * Facade 容器不动，只初始化 Capsule 连接 + 事件调度器 + Validator 工厂。
     */
    protected function bootEloquentCapsule(): void
    {
        $reflection = new \ReflectionClass(\Illuminate\Database\Capsule\Manager::class);
        $instance = $reflection->getProperty('instance')->getValue();
        if ($instance !== null) {
            return;
        }

        if (!defined('LARA_DIR')) {
            return;
        }

        $database_config = require LARA_DIR . '/config/database.php';

        $manager = new \Illuminate\Database\Capsule\Manager();
        $defaultConnection = $database_config['default'];
        foreach ($database_config['connections'] as $connection_name => $connection_config) {
            // default 连接注册为默认（无 name）；同时所有连接都按名字注册一份，
            // 这样 connection('default') 与 connection('pgsql') 都能命中
            // （Validator PresenceVerifier 等可能按连接名而非 default 取连接）。
            if ($connection_name === $defaultConnection) {
                $manager->addConnection($connection_config);
            }
            $manager->addConnection($connection_config, $connection_name);
        }
        $manager->setAsGlobal();

        $dispatcher = new \Illuminate\Events\Dispatcher();
        $manager->setEventDispatcher($dispatcher);
        $manager->bootEloquent();

        // 复刻 EloquentLoadBehavior::initializeValidator 的最小版本：为 watson/validating
        // 提供 Validator 工厂（Presence Verifier 复用 Capsule 的 database manager）。
        // 每次重新绑定 PresenceVerifier 到当前 Capsule manager：跨测试 / fork 子进程可能
        // 让 Capsule 被 setAsGlobal 替换，若缓存旧 factory 会指向失效的 manager 而报
        // 「connection not configured」。translator 工厂可复用（无连接依赖）。
        $presenceVerifier = new \Illuminate\Validation\DatabasePresenceVerifier($manager->getDatabaseManager());
        if (!isset($GLOBALS['laravel_validator_factory'])) {
            $translator = new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'zh_CN');
            $GLOBALS['laravel_validator_factory'] = new \Illuminate\Validation\Factory($translator);
        }
        $GLOBALS['laravel_validator_factory']->setPresenceVerifier($presenceVerifier);
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
