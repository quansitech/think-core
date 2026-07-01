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
            // runTp/runJob 序列化的是闭包执行后的结果（普通数据，不含闭包），
            // 用 PHP 原生 serialize 即可，无需依赖 opis/closure（v15 升级后已移除）。
            $re_serialize = serialize($result);
            $content = ['__QSCMF_TESTING_SERIALIZE_START__', $re_serialize, '__QSCMF_TESTING_SERIALIZE_END__'];
            qs_exit(implode(",", $content));
        }
        else{
            E('testingCallback is null or not a Closure');
        }
    }
}