<?php

defined('_PHP_FILE_') || define('_PHP_FILE_',  '');
defined('__ROOT__') || define('__ROOT__', env('ROOT', ''));

defined('ROOT_PATH') || define('ROOT_PATH', realpath(__DIR__ . '/../../../..'));
defined('LARA_DIR') || define('LARA_DIR', ROOT_PATH  .  '/lara');
// 定义应用目录
defined('APP_NAME') || define('APP_NAME', 'app');
defined('APP_PATH') || define('APP_PATH',ROOT_PATH . '/' . APP_NAME . '/');
defined('APP_DIR') || define('APP_DIR', ROOT_PATH . '/' . APP_NAME);
defined('WWW_DIR') || define('WWW_DIR', ROOT_PATH . '/www');
defined('TPL_PATH') || define('TPL_PATH', APP_DIR . '/Tpl/');
defined('UPLOAD_PATH') || define('UPLOAD_PATH', __ROOT__ . '/Uploads');
defined('UPLOAD_DIR') || define('UPLOAD_DIR', WWW_DIR . DIRECTORY_SEPARATOR . 'Uploads');
defined('SECURITY_UPLOAD_PATH') || define('SECURITY_UPLOAD_PATH', __ROOT__ . '/' . APP_NAME . '/Uploads');
defined('SECURITY_UPLOAD_DIR') || define('SECURITY_UPLOAD_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Uploads');
defined('RULE_DIR') || define('RULE_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Common/Rule');
defined('CRON_DIR') || define("CRON_DIR", APP_DIR . DIRECTORY_SEPARATOR . 'Cron');
defined('CODER_DIR') || define('CODER_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Common/Coder');
defined('ADDON_PATH') || define('ADDON_PATH', APP_PATH . 'Addons/');

// 系统常量定义
defined('THINK_PATH')   or define('THINK_PATH', __DIR__ . '/');
defined('APP_STATUS')   or define('APP_STATUS',     ''); // 应用状态 加载对应的配置文件
defined('APP_DEBUG') || define('APP_DEBUG', env("APP_DEBUG", false));

if(function_exists('saeAutoLoader')){// 自动识别SAE环境
    defined('APP_MODE')     or define('APP_MODE',      'sae');
    defined('STORAGE_TYPE') or define('STORAGE_TYPE',  'Sae');
}else{
    defined('APP_MODE')     or define('APP_MODE',       'common'); // 应用模式 默认为普通模式
    defined('STORAGE_TYPE') or define('STORAGE_TYPE',   'File'); // 存储类型 默认为File
}

defined('RUNTIME_PATH') or define('RUNTIME_PATH',   APP_PATH.'Runtime/');   // 系统运行时目录
defined('LIB_PATH')     or define('LIB_PATH',       realpath(THINK_PATH.'Library').'/'); // 系统核心类库目录
defined('QSCMF_PATH') or define('QSCMF_PATH', LIB_PATH . 'Qscmf/');
defined('CORE_PATH')    or define('CORE_PATH',      LIB_PATH.'Think/'); // Think类库目录
defined('BEHAVIOR_PATH')or define('BEHAVIOR_PATH',  LIB_PATH.'Behavior/'); // 行为类库目录
defined('MODE_PATH')    or define('MODE_PATH',      THINK_PATH.'Mode/'); // 系统应用模式目录
defined('VENDOR_PATH')  or define('VENDOR_PATH',    LIB_PATH.'Vendor/'); // 第三方类库目录
defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.'Common/'); // 应用公共目录
defined('CONF_PATH')    or define('CONF_PATH',      COMMON_PATH.'Conf/'); // 应用配置目录
defined('LANG_PATH')    or define('LANG_PATH',      COMMON_PATH.'Lang/'); // 应用语言目录
defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.'Html/'); // 应用静态目录
defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'Logs/'); // 应用日志目录
defined('TEMP_PATH')    or define('TEMP_PATH',      RUNTIME_PATH.'Temp/'); // 应用缓存目录
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'Data/'); // 应用数据目录
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'Cache/'); // 应用模板缓存目录
defined('CONF_EXT')     or define('CONF_EXT',       '.php'); // 配置文件后缀
defined('CONF_PARSE')   or define('CONF_PARSE',     '');    // 配置文件解析方法

