<?php
namespace Qscmf\Builder\FormType;

class FileFormType{

    private $preview_url = "https://view.officeapps.live.com/op/embed.aspx?src=";
    private $preivew_ext = [
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'
    ];

    protected function needPreview(string $file_url) : bool{
        $arr = explode('.', $file_url);
        $ext = end($arr);

        return in_array($ext, $this->preivew_ext);
    }

    protected function genPreviewUrl(string $file_url) : string{
        return $this->preview_url . $file_url;
    }

    protected function buildJsFn() : string{
        $ext_json = json_encode($this->preivew_ext, JSON_PRETTY_PRINT);
        $js = <<<javascript
function previewUrl(url){
    var ext_arr = {$ext_json};
    
    var ext = url.split('.').pop();
    if(ext_arr.includes(ext)){
        return '{$this->preview_url}' + url;
    }
    else{
        return '';
    }
}
javascript;

        return $js;
    }

}