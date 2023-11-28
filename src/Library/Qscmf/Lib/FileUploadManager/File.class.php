<?php
namespace Qscmf\Lib\FileUploadManager;

class File{
    public string $title;
    public string $file;
    public string $url;
    public int $size;
    public string $cate;
    public string $mime_type;
    public int $security;
    public int $owner;
    public int $upload_date;
    public string $hash_id;
    public string $vendor_type;

    private array $meta = [
        "file", "url", "size", "hash_id", "mime_type"
    ];

    public function __construct($fileInfo = []) {
        foreach ($fileInfo as $key => $value) {
            if(property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function merge(File $source_file){
        foreach($this->meta as $key){
            $this->{$key} = $source_file->{$key};
        }
    }
}