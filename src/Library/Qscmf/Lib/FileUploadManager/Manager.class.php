<?php
namespace Qscmf\Lib\FileUploadManager;



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
        $file = D('FilePic')->where(['hash_id' => $this->file->hash_id])->find();
        if($file){
            $this->source_file = new File($file);
            return true;
        }
        return false;
    }

    public function mirror() : int | bool{
        if(!$this->source_file){
            $r = $this->isExists();
            if($r === false){
                return false;
            }
        }

        $this->file->merge($this->source_file);

        $this->file->upload_date = time();

        $id = D("FilePic")->add((array)$this->file);
        if($id === false){
            E("文件新增失败");
        }

        return $id;
    }

    public function add() : int{
        $this->file->upload_date = time();
        $id = D("FilePic")->add((array)$this->file);
        if($id === false){
            E(D("FilePic")->getError());
        }

        return $id;
    }
}