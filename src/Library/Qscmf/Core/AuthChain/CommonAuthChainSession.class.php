<?php


namespace Qscmf\Core\AuthChain;


use Qscmf\Core\AuthChain;

class CommonAuthChainSession implements IAuthChainSession
{
    public function set($role_id,$role_type)
    {
        session(AuthChain::AUTH_RULE_ID, $role_id);
        session(AuthChain::AUTH_ROLE_TYPE, $role_type);
    }

    public function get($key){
        return session($key);
    }
    
    public function clear()
    {
        session(AuthChain::AUTH_RULE_ID, null);
        session(AuthChain::AUTH_ROLE_TYPE, null);
    }

}