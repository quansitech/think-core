<?php
namespace Qscmf\Controller;

use Think\Controller;

class ResourceController extends Controller{

    public function temporaryLoad($key, $download = 0){
        $file_path = S($key);

        if(!$file_path){
            qs_exit('没有访问权限');
        }

        if(!file_exists($file_path)){
            qs_exit('资源不存在');
        }

        $content_type = (new \Symfony\Component\Mime\MimeTypes())->guessMimeType($file_path);
        if($download){
            header('Content-Disposition: attachment; filename="'.I("get.filename").'"');
        }
        header('Content-Type: '.$content_type);
        readfile($file_path);
    }

	public function download($file_id) {
		downloadFile($file_id);
	}
}