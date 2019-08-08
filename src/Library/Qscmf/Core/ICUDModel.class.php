<?php

namespace Qscmf\Core;

interface ICUDModel{
    
    function add($data = '', $options = array(), $replace = false);
    
    function edit($data = '', $options = array(), $msg = '');
    
    function del($id = '');
}

