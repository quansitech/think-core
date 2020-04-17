<?php
namespace Qscmf\Core;

trait ModelHelper{

    public function &generator($map = [], $count = 1){
        if(!empty($map)){
            $this->where($map);
        }

        $page = 1;
        while($ents = $this->page($page, $count)->select()){
            foreach($ents as $ent){
                yield $ent;
            }
            $page++;
        }
    }
}