<?php
namespace Qscmf\Lib\FileUploadManager;

use Illuminate\Database\Capsule\Manager as Capsule;



class Manager{

    private File $file;
    private File $source_file;

    public function __construct(File $file) {
        $this->file = $file;
    }

    public function isExists() : bool{
        if(strlen($this->file->hash_id) < 32){
            return false;
        }
        $file = Capsule::table('file_pic')->where('hash_id', $this->file->hash_id)->where('vendor_type', $this->file->vendor_type)->first();
        if($file){
            $this->source_file = new File((array)$file);
            return true;
        }
        return false;
    }

    public function mirror() : string | int | bool{
        if(!$this->source_file){
            $r = $this->isExists();
            if($r === false){
                return false;
            }
        }

        $this->file->merge($this->source_file);

        $this->file->upload_date = time();

        $id = Capsule::table('file_pic')->insertGetId($this->file->toArray());
        if($id === false){
            E("文件新增失败");
        }

        return $id;
    }

    public function add() : int|string{
        $this->file->upload_date = time();
        $id = Capsule::table('file_pic')->insertGetId($this->file->toArray());
        if($id === false){
            E("文件新增失败");
        }

        return $id;
    }
}
