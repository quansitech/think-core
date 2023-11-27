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
        RegisterContainer::registerHeadCss(__ROOT__ . '/Public/libs/Huploadify/Huploadify.css');
        RegisterContainer::registerHeadJs(__ROOT__. '/Public/libs/Huploadify/Huploadify.js');
        RegisterContainer::registerHeadJs(__ROOT__ . '/Public/libs/file-md5-wasm/dist/index.js');
        RegisterContainer::registerHeadJs(__ROOT__ . '/Public/libs/qsFileHelper/index.js');
    }

    protected function loadDBConfig() : void{
        try{
            \Qscmf\Lib\Tp3Resque\Resque\Event::listen('beforePerform', function($args){
                readerSiteConfig();
            });
            readerSiteConfig();
        }
        catch(\Exception $ex){
            if(IS_CGI) {
                E($ex->getMessage());
            }
        }
    }


}
