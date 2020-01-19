<?php
namespace Larafortp\Provider;

use Illuminate\Support\ServiceProvider;
use Larafortp\Commands\QscmfDiscoverCommand;

class QscmfServiceProvider extends ServiceProvider
{
    protected $commands = [
        QscmfDiscoverCommand::class
    ];

    public function register(){
        $this->commands($this->commands);
    }
}