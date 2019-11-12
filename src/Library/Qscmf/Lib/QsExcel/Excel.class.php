<?php
namespace Qscmf\Lib\QsExcel;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Qscmf\Lib\QsExcel\Builder\BuilderContract;
use Qscmf\Lib\QsExcel\Builder\IBuilder;
use Qscmf\Lib\QsExcel\Loader\LoaderContract;

class Excel{

    protected $spread_sheet;
    protected $builder;
    protected $loader;

    public function __construct()
    {
        if(!class_exists(Spreadsheet::class)){
            E('require phpoffice/phpspreadsheet, you should install from composer');
        }

        $this->spread_sheet = new Spreadsheet();
    }

    public function setBuild(BuilderContract $builder){
        $this->builder = $builder;
        $this->builder->setSpreadSheet($this->spread_sheet);
        return $this;
    }
    
    public function setLoader(LoaderContract $loader){
        $this->loader = $loader;
        return $this;
    }

    public function load(string $file){
        return $this->loader->load($file);
    }

    public function output(string $file_name){
        $this->builder->build();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0


        $writer = IOFactory::createWriter($this->spread_sheet, "Xlsx");
        $writer->save('php://output');
    }

    public function __call($method,$args){
        if(method_exists($this->spread_sheet, $method)){
            return call_user_func_array(array($this->spread_sheet,$method), $args);
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
}