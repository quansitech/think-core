<?php
namespace Qscmf\Builder\ColumnType\Datetime;

use Illuminate\Support\Str;
use Qscmf\Builder\ColumnType\ColumnType;
use Qscmf\Builder\ColumnType\Date\Date;
use Qscmf\Builder\ColumnType\EditableInterface;

class Datetime extends Date {
    protected string $_template =  __DIR__ . '/datetime.html';
    protected string $_default_format =  'Y-m-d H:i:s';
}