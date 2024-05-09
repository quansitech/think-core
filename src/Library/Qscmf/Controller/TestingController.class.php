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
            $content = ['__QSCMF_TESTING_SERIALIZE_START__', $re_serialize, '__QSCMF_TESTING_SERIALIZE_END__'];
            qs_exit(implode(",", $content));
        }
        else{
            E('testingCallback is null or not a Closure');
        }
    }
}