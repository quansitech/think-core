<?php
namespace Qscmf\Core;

trait ModelHelper{

    protected $effecient_cache_arr = [];

    public function getFieldForN1(array $map, string $field, int | string $primary_key, string $show_field = ""): string | array | int | float | null{
        $hash_key = md5(json_encode($map) . $field);

        if(isset($this->effecient_cache_arr[$hash_key])){
            return $show_field ? $this->effecient_cache_arr[$hash_key][$primary_key][$show_field] : $this->effecient_cache_arr[$hash_key][$primary_key];
        }
        else{
            $list = $this->where($map)->field($field)->select();
            $arr = [];
            foreach($list as $v){
                $arr[$v[$this->getPk()]] = $v;
            }
            $this->effecient_cache_arr[$hash_key] = $arr;
            return $show_field ? $this->effecient_cache_arr[$hash_key][$primary_key][$show_field] : $this->effecient_cache_arr[$hash_key][$primary_key];
        }
    }
    
    public function &generator($map = [], $count = 1){
        if(!empty($map)){
            $this->where($map);
        }

        $page = 1;
        $options = $this->options;
        while($ents = $this->page($page, $count)->select()){
            foreach($ents as $ent){
                yield $ent;
            }
            $page++;
            $this->options = $options;
        }
    }
}