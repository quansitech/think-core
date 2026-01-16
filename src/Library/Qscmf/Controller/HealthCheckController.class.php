<?php

namespace Qscmf\Controller;

use Think\Controller;

class HealthCheckController extends Controller
{ 
    public function resque(){
        $queue = I('get.queue');
        if (!$queue){
            $this->error('参数错误');
        }

        $tick = S('health_tick_queue_'.$queue);

        if (time() - $tick > 60){
            send_http_status(500);
            echo 'ERROR: queue check fail - '.$queue;
            return;
        }

        echo $tick. ' health check success.';
    }
}