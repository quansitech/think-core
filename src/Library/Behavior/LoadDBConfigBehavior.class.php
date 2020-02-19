<?php

namespace Behavior;

class LoadDBConfigBehavior{
    
     //行为执行入口
    public function run(&$param){
        try{
            readerSiteConfig();
        }
    	catch(\Exception $ex){
            
        }
    }
}
