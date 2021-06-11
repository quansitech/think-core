<?php

namespace Behavior;

class LoadDBConfigBehavior{
    
     //行为执行入口
    public function run(&$param){
        try{
            \Qscmf\Lib\Tp3Resque\Resque\Event::listen('beforePerform', function($args){
                readerSiteConfig();
            });
            readerSiteConfig();
        }
    	catch(\Exception $ex){
            
        }
    }
}
