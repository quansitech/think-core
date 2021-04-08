<?php
namespace Larafortp;

use Illuminate\Support\Facades\DB;
use Larafortp\CmmMigrate\CmmProcess;

class ConfigGenerator{

    const NUM = 'num';
    const TEXT = 'text';
    const ARRAY = 'array';
    const PICTURE = 'picture';
    const UEDITOR = 'ueditor';
    const SELECT = 'select';

    static private function strToArr($group){
        $array = preg_split('/[,;\r\n]+/', trim($group, ",;\r\n"));
        if (strpos($group, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }

    static private function arrToStr($group_arr){
        $group = '';
        foreach($group_arr as $key => $value){
            $group .= $key . ':' . $value . PHP_EOL;
        }
        return trim($group, PHP_EOL);
    }

    static function addGroup($name){
        $group = DB::table('qs_config')->where('name', 'CONFIG_GROUP_LIST')->value('value');
        $group_arr = self::strToArr($group);
        $keys = array_keys($group_arr);
        $max_id = $keys[count($group_arr) - 1];
        $max_id++;
        $group_arr[$max_id] = $name;
        DB::table('qs_config')->where('name', 'CONFIG_GROUP_LIST')->update(['value' => self::arrToStr($group_arr)]);
        return $max_id;
    }

    static function deleteGroup($name){
        $group = DB::table('qs_config')->where('name', 'CONFIG_GROUP_LIST')->value('value');
        $group_arr = self::strToArr($group);
        $group_arr = collect($group_arr)->filter(function($item) use ($name){
            return $item != $name;
        })->all();
        DB::table('qs_config')->where('name', 'CONFIG_GROUP_LIST')->update(['value' => self::arrToStr($group_arr)]);
    }

    static function addNum($name, $title, $value, $remark = '', $group = 1, $sort = 0){
        self::add($name, self::NUM, $title, $group, '', $remark, $value, $sort);
    }

    static function addText($name, $title, $value, $remark = '', $group = 1, $sort = 0){
        self::add($name, self::TEXT, $title, $group, '', $remark, $value, $sort);
    }

    static function addArray($name, $title, $value, $remark = '', $group = 1, $sort = 0){
        self::add($name, self::ARRAY, $title, $group, '', $remark, $value, $sort);
    }

    static function addPicture($name, $title, $value, $remark = '', $group = 1, $sort = 0){
        self::add($name, self::PICTURE, $title, $group, '', $remark, $value, $sort);
    }

    static function addUeditor($name, $title, $value, $remark = '', $group = 1, $sort = 0){
        self::add($name, self::UEDITOR, $title, $group, '', $remark, $value, $sort);
    }

    static function addSelect($name, $title, $value, $options, $remark = '', $group = 1, $sort = 0){
        $extrea = self::arrToStr($options);
        self::add($name, self::SELECT, $title, $group, $extrea, $remark, $value, $sort);
    }

    static function add($name, $type, $title, $group, $extra, $remark, $value, $sort){
        $create_time = time();
        $update_time = time();
        $status = 1;
        
        $arr = ['name', 'type', 'title', 'group', 'extra', 'remark', 'create_time', 'update_time', 'status', 'value', 'sort'];
        DB::table('qs_config')->insert(compact($arr));

        $process = new CmmProcess();
        $process->setTimeOut(30)->callTp(LARA_DIR . '/../www/index.php', '/Qscmf/ConfigCache/clear');
    }

    static function delete($name){
        DB::table('qs_config')->where('name', $name)->delete();
    }
}