<?php

namespace Behavior;

use Think\Exception;

class LoadDBConfigBehavior{
    
     //行为执行入口
    public function run(&$param){
        try{
            readerSiteConfig();
        }
    	catch(\Exception $ex){
            
        }
        catch( Exception $ex){

        }
    }
}
