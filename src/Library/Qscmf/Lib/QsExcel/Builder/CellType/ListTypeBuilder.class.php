<?php
namespace Qscmf\Lib\QsExcel\Builder\CellType;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ListTypeBuilder extends TypeBuilderContract
{
    public function build($header_option){
        $objValidation = $this->cell->getDataValidation();
        $objValidation->setType(DataValidation::TYPE_LIST)
            ->setAllowBlank(false)
            ->setShowDropDown(true)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setFormula1('"' . $header_option['data_source'] . '"');
    }
}