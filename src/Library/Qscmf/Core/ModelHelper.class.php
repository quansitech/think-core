<?php
namespace Qscmf\Core;

trait ModelHelper{

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