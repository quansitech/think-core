<?php
namespace Qscmf\Builder\ListSearchType\DateTime;

use Qscmf\Builder\ListSearchType\ListSearchType;
use Qscmf\Builder\ListSearchType\Select\Select;
use Qscmf\Builder\ListSearchType\SelectText\SelectText;
use Think\View;

class DateTime implements ListSearchType{

    public function build(array $item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/date_time.html');
        return $content;
    }

    //样例代码 $map = array_merge($map, DateRange::parse('date_range_data', 'create_date', $get_data));
    static public function parse(string $key, string $map_key, array $get_data) : array{
        if(isset($get_data[$key])){
            $date_range = explode('-', $get_data[$key]);
            $start_time = strtotime(trim($date_range[0]));
            $end_time = strtotime(trim($date_range[1]).'+1 day') -1;
            return [$map_key => ['BETWEEN', [$start_time, $end_time]]];
        }
        else{
            return [];
        }
    }
}