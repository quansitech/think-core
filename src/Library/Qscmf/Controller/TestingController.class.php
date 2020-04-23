<?php
namespace Qscmf\Controller;

use Think\Controller;

class TestingController extends Controller{

    public function index(){
        global $testingCallback;
        if(!isTesting()){
            qs_exit('');
        }

        if($testingCallback instanceof \Closure){
            $result = call_user_func($testingCallback);
            $re_serialize = \Opis\Closure\serialize($result);
            qs_exit($re_serialize);
        }
        else{
            E('testingCallback is null or not a Closure');
        }
    }
}