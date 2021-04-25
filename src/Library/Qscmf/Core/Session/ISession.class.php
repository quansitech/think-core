<?php

namespace Qscmf\Core\Session;

interface ISession
{
    public function set($key,$value);
    public function get($key);
    public function clear($key);

}