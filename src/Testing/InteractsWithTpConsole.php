<?php
namespace Testing;

trait InteractsWithTpConsole{

    public function cli(...$args){
        global $argv;

        $command = $args[0];
        $argv = $args;

        return $this->runTpCliAsSanbox($command);
    }

    protected function runTpCliAsSanbox($command){
        global $argv;
        global $_SERVER;

        $pipePath = "/tmp/test.pipe";

        if( file_exists( $pipePath ) ){
            unlink($pipePath);
        }

        if( !posix_mkfifo( $pipePath, 0666 ) ){
            exit('make pipe false!' . PHP_EOL);
        }

        $pid = pcntl_fork();

        if( $pid == 0 ){
            define("IS_CGI", 0);
            define("IS_CLI", true);
            $_SERVER['argv'] = $argv;
            try {
                require $this->projectPath() . '/' . $command;

                // 用 ob_get_clean() 取走并清空缓冲区，避免 exit() 时残留 ob 层级
                // 被 PHP 刷新到与父进程共享的 stdout，导致响应内容泄漏到命令行。
                $content = ob_get_clean();
            } catch (\Throwable $e) {
                // 子进程内 require 抛出的异常必须在此捕获，否则会冒泡到子进程继承的
                // PHPUnit 测试循环，导致子进程继续执行后续测试方法并嵌套 fork（进而触发
                // “Constant IS_CGI already defined” 等串扰）。捕获后丢弃半截输出，把异常
                // 信息作为响应内容回传给父进程。
                ob_end_clean();
                $content = (method_exists($e, 'getMessage') ? $e->getMessage() : (string)$e);
            }

            $file = fopen( $pipePath, 'w' );
            fwrite( $file, $content);
            exit();
        }else{
            $file = fopen( $pipePath, 'r' );
            $content = fread( $file, 99999999 ) . PHP_EOL;
            pcntl_wait($status);
        }

        return $content;
    }

    public function runTp(\Closure $callback){
        global $argv;
        global $testingCallback;

        $testingCallback = $callback;
        $command = 'www/index.php';
        $args[1] = '/Qscmf/Testing/index';
        $argv = $args;

        $re_serialize = $this->runTpCliAsSanbox($command);
        $re_serialize = $this->_extraSerializeString($re_serialize);

        // 子进程用 PHP 原生 serialize 回传闭包执行结果（普通数据），这里原生反序列化即可，
        // 无需依赖 opis/closure（v15 升级后已移除该库）。
        // 注意 _extraSerializeString 找不到标记时返回空串，原生 unserialize('') 会报错，
        // 故空串直接返回，避免反序列化告警。
        return $re_serialize !== '' ? unserialize($re_serialize) : null;
    }

    private function _extraSerializeString(string $serialize_string):?string{
        $r = preg_match('/__QSCMF_TESTING_SERIALIZE_START__,(.*),__QSCMF_TESTING_SERIALIZE_END__/', $serialize_string, $matches);

        if ($r && !empty($matches)) {
            return $matches[1];
        }
        return "";
    }

    public function runJob(string $class_name, string $job_args){
        if (empty($class_name)){
            exit('require class name!' . PHP_EOL);
        }

        return $this->runTp(function() use($class_name, $job_args){
            $class_obj = new $class_name();
            $class_obj->args = json_decode($job_args,true);
            return $class_obj->perform();
        });
    }
}