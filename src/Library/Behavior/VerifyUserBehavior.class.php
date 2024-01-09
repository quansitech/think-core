<?php

namespace Behavior;

use Gy_Library\DBCont;

class VerifyUserBehavior
{

    public function run(){
        //非正常状态用户禁止登录后台
        $user_ent = D(C('USER_AUTH_MODEL'))->find(session(C('USER_AUTH_KEY')));
        if((int)$user_ent['status'] !== DBCont::NORMAL_STATUS){
            E('用户状态异常');
        }
    }

}