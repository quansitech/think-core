<?php


namespace Qscmf\Builder\ListSearchType\Select;


class SelectBuilder
{
    public $data;
    public $placeholder = '';
    public $width = '130px';
    public $show_placeholder = true;

    public function __construct($data){
        $this->setData($data);
    }

    public function hidePlaceholder(){
        $this->show_placeholder = false;
        return $this;
    }

    public function setData(array $data):self{
        $this->data = $data;
        return $this;
    }

    public function setPlaceholder(string $placeholder):self{
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder():string{
        return $this->placeholder;
    }

    public function setWidth(string $width):self{
        $this->width = $width;
        return $this;
    }

    public function toArray():array{
        return [
            'data' => $this->data,
            'placeholder' => $this->placeholder,
            'width' => $this->width,
        ];
    }

}