<?php
namespace Qscmf\Controller;

use Think\Controller;

class ConfigCacheController extends Controller {

    public function clear(){
        S('DB_CONFIG_DATA', null);
    }
}