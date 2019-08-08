<?php

namespace Qscmf\Core;

interface IPageModel{
    
    function getListForCount($map);
    
    function getListForPage($map, $page, $list_rows);
}

