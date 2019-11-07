<?php
namespace Qscmf\Lib\QsExcel\Builder\CellType;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

abstract class TypeBuilderContract
{
    protected $cell;

    public function __construct(Cell $cell)
    {
        $this->cell = $cell;
    }

    abstract function build(array $header_option);
}