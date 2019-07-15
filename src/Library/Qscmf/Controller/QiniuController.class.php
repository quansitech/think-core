<?php
namespace Qscmf\Controller;

use GuzzleHttp\Client;
use Qiniu\Auth;
use Think\Controller;

class QiniuController extends Controller{

    public function checkTranscode($file_id){
        $file_ent = M('FilePic')->find($file_id);
        if($file_ent['ref_info']){

            if($file_ent['ref_status'] == 0){
                $data['name'] = $file_ent['title'];
                $data['url'] = $file_ent['url'];
                $data['size'] = $file_ent['size'];
                $data['status'] = 1;
            }
            else{
                $data['error'] = $file_ent['ref_info'];
                $data['status'] = 2;
            }
        }
        else{
            $data['status'] = 0;
        }
        $this->ajaxReturn($data);
    }

    public function callback(){
        $ak = env('QINIU_AK');
        $sk = env('QINIU_SK');

        $auth = new Auth($ak, $sk);

        $callbackBody = file_get_contents('php://input');

        $contentType = 'application/x-www-form-urlencoded';

        $authorization = $_SERVER['HTTP_AUTHORIZATION'];

        $url =HTTP_PROTOCOL . '://' . SITE_URL  . U('qscmf/qiniu/callback');
        $isQiniuCallback = $auth->verifyCallback($contentType, $authorization, $url, $callbackBody);
        if (!$isQiniuCallback) {
            E("is not qiniucallback");
        }

        $type = I('post.type');
        $config = C('UPLOAD_TYPE_' . strtoupper($type));
        if(!$config){
            E('cant get type config');
        }

        $file_url = $config['domain'] . '/' . I('post.key');

        $client = new Client();
        $response = $client->request('GET', $file_url. "?avinfo");
        $body = $response->getBody()->getContents();
        $body = json_decode($body, true);


        $data['title'] = I('post.fname');
        $data['file'] = '';
        $data['url'] = $file_url;
        $data['ref_id'] = I('post.persistentId', '');
        $data['upload_date'] = time();
        $data['cate'] = $type;
        $data['size'] = I('post.fsize');
        $data['duration'] = $body['format']['duration'] ?? 0;

        $id = M("FilePic")->add($data);
        $return['file_id'] = $id;
        $return['ref_id'] = $data['ref_id'];
        $this->ajaxReturn($return);
    }

    public function notify(){
        $data = json_decode(file_get_contents('php://input'), true);

        $map['ref_id'] = $data['id'];
        $map['ref_info'] = '';

        $file_ent = M("FilePic")->where($map)->find();
        $config = C('UPLOAD_TYPE_' . strtoupper($file_ent['cate']));
        $file_ent['ref_status'] = $data['code'];
        if(isset($data['items'][0]['key'])){
            $file_ent['url'] = $config['domain'] . '/' . $data['items'][0]['key'];
        }
        $file_ent['ref_info'] = file_get_contents('php://input');

        if($data['code'] == 0){
            $client = new Client();
            $response = $client->request('GET', $file_ent['url'] . "?avinfo");
            $body = $response->getBody()->getContents();
            $body = json_decode($body, true);
            $title = substr($file_ent['title'], 0, strripos ($file_ent['title'], '.') );
            $file_ent['title'] =$title . '.' . explode(',', $body['format']['format_name'])[0];
            $file_ent['size'] = $body['format']['size'];
        }


        M("FilePic")->save($file_ent);

    }

    //https://****/api/qiniu/upToken/type/audio
    // { token : ****, key: ****, type: 'audio', mimes: 'audio/mp3, audio/m4a'}
    public function upToken($type){
        $config = C('UPLOAD_TYPE_' . strtoupper($type));

        $ak = env('QINIU_AK');
        $sk = env('QINIU_SK');

        $auth = new Auth($ak, $sk);

        $bucket = $config['bucket'];

        $callbackBody = 'key=$(key)&fname=$(fname)&persistentId=$(persistentId)&type=$(x:type)&fsize=$(fsize)';

        $pfopOps = $config['pfopOps'];
        $policy = array(
            'callbackUrl' => HTTP_PROTOCOL . '://' . SITE_URL  . U('qscmf/Qiniu/callback'),
            'callbackBody' =>$callbackBody,
            'persistentOps' => $pfopOps,
            'persistentNotifyUrl' => HTTP_PROTOCOL . '://' . SITE_URL . U('qscmf/Qiniu/notify'),
            'persistentPipeline' => $config['pipeline'],
            'mimeLimit' => str_replace(',', ';', $config['mimes']),
            'fsizeLimit' => $config['maxSize']
        );

        $token = $auth->uploadToken($bucket, null, 3600, $policy);
        $this->ajaxReturn([
            'token' => $token,
            'key' => $this->_getName($config['saveName']),
            'type' => $type,
            'mimes' => $config['mimes']
        ]);
    }

    protected function genKey($config, $ext = ''){
        $sub_name = self::_getName($config['subName']);
        $pre_path = $config['savePath'] . $sub_name .'/';
        $save_name = self::_getName($config['saveName']);
        $dir = trim(trim($pre_path . $save_name, '.'), '/');
        if($ext){
            $dir .= $ext;
        }
        return $dir;
    }

    private static function _getName($rule){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }
}
