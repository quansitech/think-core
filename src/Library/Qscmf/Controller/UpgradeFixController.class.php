<?php
namespace Qscmf\Controller;

use Qscmf\Lib\Tp3Resque\Resque;
use Think\Controller;

class UpgradeFixController extends Controller{

    public function v300FixSchedule($queue){
        $keys = Resque::redis()->hkeys($queue. '_schedule');
        foreach($keys as $v){
            $s = Resque::redis()->hget($queue. '_schedule', $v);
            $schedule = json_decode($s, true);
            Resque::redis()->zadd($queue. '_schedule_sort', $schedule['run_time'], $v);
        }
        echo 'finished' . PHP_EOL;
    }
}