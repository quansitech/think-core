<?php
namespace Qscmf\Builder\ListSearchType\DateRange;

use Qscmf\Builder\ListSearchType\ListSearchType;
use Qscmf\Builder\ListSearchType\Select\Select;
use Qscmf\Builder\ListSearchType\SelectText\SelectText;
use Think\View;

class DateRange implements ListSearchType{

    public function build(array $item){
        $view = new View();
        $view->assign('item', $item);
        $content = $view->fetch(__DIR__ . '/date_range.html');
        return $content;
    }

    //样例代码 $map = array_merge($map, DateRange::parse('date_range_data', 'create_date', $get_data));
    static public function parse(string $key, string $map_key, array $get_data) : array{
        return self::parseForDecimal($key, $map_key, $get_data);
    }


    //数据库格式为decimal
    static public function parseForDecimal(string $key, string $map_key, array $get_data) : array{
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

    //数据库格式为timestamp
    static public function parseForTimestamp(string $key, string $map_key, array $get_data) : array{
        if(isset($get_data[$key])){
            $date_range = explode('-', $get_data[$key]);
            $start_time = trim($date_range[0]);
            $end_time = trim($date_range[1]) . " 23:59:59.9999";
            return [$map_key => ['BETWEEN', [$start_time, $end_time]]];
        }
        else{
            return [];
        }
    }
}