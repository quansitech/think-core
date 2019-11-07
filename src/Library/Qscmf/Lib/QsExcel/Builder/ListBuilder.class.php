<?php
namespace Qscmf\Lib\QsExcel\Builder;

use Qscmf\Lib\QsExcel\Builder\CellType\ListTypeBuilder;

class ListBuilder extends BuilderContract {
    
    protected $type_map = [
        'list' => ListTypeBuilder::class
    ];

    public function __construct(array $options, array $data = [])
    {
        $this->setOptions($options);
        if(array_filter($data)){
            $this->setData($data);
        }
    }

    public function setOptions($options){
        $this->options = $options;
    }

    public function setData(array $data){
        $this->data = $data;
    }

    public function build(){
        $row_count = $this->options['row_count'] ?? 0;
        $row = 1;
        $sheet = $this->spread_sheet->getActiveSheet();
        for($i = 0; $i < $row_count + 1; $i++, $row++){
            $col = 'A';
            foreach($this->options['headers'] as $header){
                //fill header
                if($row == 1){
                    $sheet ->setCellValue($col . $row, $header['title']);
                    $col++;
                    continue;
                }

                if(isset($this->type_map[$header['type']])){
                    $type_builder =  new $this->type_map[$header['type']]($sheet->getCell($col . $row));
                    $type_builder->build($header);
                }

                $col++;
            }
        }

        $this->buildData();
    }
}