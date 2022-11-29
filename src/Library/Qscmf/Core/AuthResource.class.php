<?php
namespace Qscmf\Core;

class AuthResource{

    public static function genTemporaryUrl(array $file_ent, int $expired){
        $param = [];
        $param['expired'] = time() + $expired;
        $param['file_id'] = $file_ent['id'];

        $config = C("UPLOAD_TYPE_" . strtoupper($file_ent['cate']));

        $file_path = normalizeRelativePath(WWW_DIR . '/' . $config['rootPath'] . $file_ent['file']);

        $data = [];
        $data['file_path'] = $file_path;

        $key = md5(http_build_query($param));

        S($key, $file_path, $expired);

//        $ext = pathinfo($file_path, PATHINFO_EXTENSION);

        return U("qscmf/resource/temporaryLoad", ['key' => $key, 'resource' => $file_ent['file']], false, true);
    }
}