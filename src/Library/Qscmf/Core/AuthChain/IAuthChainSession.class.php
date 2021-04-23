<?php

namespace Qscmf\Core\AuthChain;

interface IAuthChainSession
{
    public function set($key,$value);
    public function get($key);
    public function clear($key);

}