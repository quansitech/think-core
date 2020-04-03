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
            require ROOT_PATH . '/' . $command;

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
}