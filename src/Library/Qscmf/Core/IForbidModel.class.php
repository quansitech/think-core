<?php

namespace Qscmf\Core;

interface IForbidModel{
    
    function forbid($id);
    
    function resume($id);
}
