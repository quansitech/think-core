<?php
namespace Qscmf\Builder\FormType;

class FileFormType{

    private $preview_url = "https://view.officeapps.live.com/op/embed.aspx?src=";
    private $preivew_ext = [
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'jpg', 'jpeg', 'png', 'gif'
    ];

    private $office_ext = [
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'
    ];

    private $other_ext = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif'
    ];

    protected function getExt($url){
        $arr = explode('.', $url);
        $ext = end($arr);
        return $ext;
    }

    protected function needPreview(string $file_url) : bool{
        $ext = $this->getExt($file_url);

        return in_array($ext, $this->preivew_ext);
    }

    protected function genPreviewUrl(string $file_url) : string{
        $ext = $this->getExt($file_url);
        if(in_array($ext, $this->office_ext)){
            return $this->preview_url . $file_url;
        }
        else if(in_array($ext, $this->other_ext)){
            return $file_url;
        }
        else{
            return '';
        }
    }

    protected function buildJsFn() : string{
        $office_ext = json_encode($this->office_ext, JSON_PRETTY_PRINT);
        $other_ext = json_encode($this->other_ext, JSON_PRETTY_PRINT);
        $js = <<<javascript
function previewUrl(url){
    var office_ext_arr = {$office_ext};
    var other_ext_arr = {$other_ext};
    
    var ext = url.split('.').pop();
    if(office_ext_arr.includes(ext)){
        return '{$this->preview_url}' + url;
    }
    else if(other_ext_arr.includes(ext)){
        return url;
    }
    else {
        return '';
    }
}
javascript;

        return $js;
    }

}