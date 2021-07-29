<?php


namespace Behavior;


class InitAuthChainBehavior
{
    public function run(&$_data){
        \Qscmf\Core\AuthChain::init();
    }

}