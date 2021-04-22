<?php

namespace Qscmf\Core\AuthChain;

interface IAuthChainSession
{
    public function set($role_id,$role_type);
    public function get($key);
    public function clear();

}