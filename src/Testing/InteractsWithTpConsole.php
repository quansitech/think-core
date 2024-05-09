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
            require __DIR__ . '/../../../../../' . $command;

            $content = ob_get_contents();

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

        return !is_null($re_serialize) ? \Opis\Closure\unserialize($re_serialize, null) : $re_serialize;
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