<?php
namespace Larafortp\CmmMigrate;

use \Symfony\Component\Process\PhpExecutableFinder;
use \Symfony\Component\Process\Process;


class CmmProcess{

    protected $phpBinaryPath;
    protected $timeout = 60;

    public function __construct()
    {
        $phpBinaryFinder = new PhpExecutableFinder();
        $this->phpBinaryPath = $phpBinaryFinder->find();
    }

    public function setTimeOut($timeout){
        $this->timeout = $timeout;
        return $this;
    }

    public function callTp($script, ...$param){
        $commands = [$this->phpBinaryPath, $script];
        $commands = array_merge($commands, $param);
        $process = new Process($commands);
        $process->setTimeout($this->timeout);
        $process->mustRun();

        echo $process->getOutput();
    }
}