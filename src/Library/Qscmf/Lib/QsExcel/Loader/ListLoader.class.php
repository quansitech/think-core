<?php
namespace Qscmf\Lib\QsExcel\Loader;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ListLoader extends LoaderContract
{
    public function load($file){
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $return_list = [];
         foreach($sheetData as $data){
             if(array_filter(collect($data)->flatten()->all())){
                 $return_list[] = $data;
             }
         }
         return $return_list;
    }
}