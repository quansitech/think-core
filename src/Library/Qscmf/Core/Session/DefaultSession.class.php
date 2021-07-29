<?php


namespace Qscmf\Core\Session;


class DefaultSession implements ISession
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