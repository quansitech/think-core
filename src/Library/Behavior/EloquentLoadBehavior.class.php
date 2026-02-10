<?php
namespace Behavior;

use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Translation\Translator;
use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\DatabasePresenceVerifier;

/**
 * 行为扩展：加载Eloquent及相关Laravel组件
 */
class EloquentLoadBehavior {
    public function run(&$params) {
        $database_config = require LARA_DIR . '/config/database.php';

        $manager = new CapsuleManager();

        foreach ($database_config['connections'] as $connection_name => $connection_config) {
            if($database_config['default'] === $connection_name) {
                $manager->addConnection($connection_config);
                continue;
            }
            $manager->addConnection($connection_config, $connection_name);
        }

        $manager->setAsGlobal();

        // 初始化事件调度器（watson/validating 需要）
        $dispatcher = new Dispatcher();
        $manager->setEventDispatcher($dispatcher);

        $manager->bootEloquent();

        // 初始化 Facade 系统（watson/validating 的 ValidatingObserver 需要 Event Facade）
        $this->initializeFacades($dispatcher);

        // 初始化 Validator（watson/validating 需要）
        $this->initializeValidator($manager);
    }

    /**
     * 初始化 Laravel Facade 系统
     */
    private function initializeFacades($dispatcher) {
        // 创建 Container
        $container = new Container();

        // 绑定事件调度器到容器
        $container->instance('events', $dispatcher);

        // 设置 Facade 的容器
        Event::setFacadeApplication($container);
    }

    /**
     * 初始化 Laravel Validator
     */
    private function initializeValidator($manager) {
        // 创建 Presence Verifier（用于 unique、exists 等验证规则）
        $presenceVerifier = new DatabasePresenceVerifier($manager->getDatabaseManager());

        // 创建文件加载器用于翻译验证消息
        $loader = new FileLoader(new Filesystem(), LARA_DIR . '/lang');

        // 创建翻译器（使用中文）
        $translator = new Translator($loader, 'zh_CN');

        // 创建验证工厂
        $factory = new ValidationFactory($translator);

        // 设置 Presence Verifier
        $factory->setPresenceVerifier($presenceVerifier);

        // 设置为全局可访问（通过静态变量）
        if (!class_exists('Illuminate\Support\Facades\Validator', false)) {
            // 如果 Facade 不存在，直接设置到模型中
            // 这将在模型使用 getValidator() 时使用
            $GLOBALS['laravel_validator_factory'] = $factory;
        }
    }
}