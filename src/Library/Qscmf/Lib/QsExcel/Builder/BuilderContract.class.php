<?php
namespace Qscmf\Lib\QsExcel\Builder;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class BuilderContract
{
    protected $spread_sheet;

    public function setSpreadSheet(Spreadsheet $spread_sheet){
        $this->spread_sheet = $spread_sheet;
    }

    protected function buildData(){
        if(array_filter($this->data)){
            $this->spread_sheet->getActiveSheet()->fromArray($this->data, null, 'A2');
        }

    }

    abstract  function build();
}