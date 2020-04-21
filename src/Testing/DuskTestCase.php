<?php

namespace Testing;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase;

abstract class DuskTestCase extends TestCase
{
    use InteractsWithDatabase;
    use DBTrait;

    protected $serverProcess;

    abstract public function laraPath():string;

    /**
     * 创建 RemoteWebDriver 实例.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions())->addArguments([
            '--disable-gpu',
            '--disable-impl-side-painting',
            '--disable-gpu-sandbox',
            '--disable-accelerated-2d-canvas',
            '--disable-accelerated-jpeg-decoding',
            '--no-sandbox',
//            '--disable-gpu',
            '--headless',
//            '--no-sandbox',
//            '--window-size=1680,1050',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY, $options
        )
        );
    }

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

    public function setUp() : void
    {
        static::startChromeDriver();

        parent::setUp();

        Browser::$storeScreenshotsAt = base_path('tests/Browser/screenshots');
        Browser::$storeConsoleLogAt = base_path('tests/Browser/console');

        $this->uninstall();
        $this->install();

        $this->runServer();
    }

    public function tearDown() : void
    {
        $this->uninstall();
        static::tearDownDuskClass();

        parent::tearDown();
    }



    protected function runServer()
    {
        $phpBinaryFinder = new \Symfony\Component\Process\PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();

        $host = str_replace('http://', '', $this->app['config']['app.url']);
        $host = str_replace('https://', '', $host);

        chdir(base_path('../www'));

        $this->serverProcess = new \Symfony\Component\Process\Process([$phpBinaryPath, '-S', $host, base_path('server.php')]);
        $this->serverProcess->start();
    }
}
