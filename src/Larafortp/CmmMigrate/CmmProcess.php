<?php
namespace Larafortp\CmmMigrate;

use \Symfony\Component\Process\PhpExecutableFinder;
use \Symfony\Component\Process\Process;

/**
 * @deprecated 在v12版本后移出核心， 请使用 https://github.com/quansitech/qscmf-utils 的 CmmProcess 代替
 */
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