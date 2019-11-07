<?php
namespace Qscmf\Lib\QsExcel\Loader;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class LoaderContract
{
    abstract public function load(string $file);
}