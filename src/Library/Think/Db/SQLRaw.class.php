<?php
namespace Think\Db;

class SQLRaw{

    protected $sql;

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function __toString() {
        return $this->sql;
    }
}