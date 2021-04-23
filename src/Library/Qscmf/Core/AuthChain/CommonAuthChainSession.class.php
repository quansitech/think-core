<?php


namespace Qscmf\Core\AuthChain;


use Qscmf\Core\AuthChain;

class CommonAuthChainSession implements IAuthChainSession
{
    public function set($key, $value)
    {
        session($key, $value);
    }

    public function get($key){
        return session($key);
    }
    
    public function clear($key)
    {
        $this->set($key,null);
    }

}