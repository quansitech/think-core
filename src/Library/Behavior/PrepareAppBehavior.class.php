<?php

namespace Behavior;

use Bootstrap\RegisterContainer;

class PrepareAppBehavior{
    
     //行为执行入口
    public function run(&$param) : void{
        $this->loadDBConfig();
        $this->baseRegister();
    }

    protected function baseRegister(){
        RegisterContainer::registerHeadCss(__ROOT__ . '/Public/libs/viewerjs/viewer.min.css');
        RegisterContainer::registerHeadJs(__ROOT__. '/Public/libs/viewerjs/viewer.min.js');
    }

    protected function loadDBConfig() : void{
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
