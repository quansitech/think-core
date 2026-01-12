<?php

namespace Behavior;

use Gy_Library\DBCont;
use Illuminate\Database\Capsule;

class VerifyUserBehavior
{

    public function run(){
        //非正常状态用户禁止登录后台
        $user_ent = Capsule::table(C('USER_AUTH_MODEL'))->find(session(C('USER_AUTH_KEY')));
        if((int)$user_ent->status !== DBCont::NORMAL_STATUS){
            E('用户状态异常');
        }
    }

}