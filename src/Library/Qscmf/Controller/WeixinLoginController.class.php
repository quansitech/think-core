<?php


namespace Qscmf\Controller;


use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Qscmf\Core\QsController;
use Qscmf\Lib\WeixinLogin;

class WeixinLoginController extends QsController
{
    public function scan($goto_url='',$mobile_goto_url=''){
        $this->_template = __DIR__ .'/../View/WeixinLogin/scan.html';
        if (!$goto_url){
            $goto_url=U('/',[],true,true);
        }else{
            $goto_url=urldecode($goto_url);
        }
        if (!$mobile_goto_url){
            $mobile_goto_url=U('/',[],true,true);
        }else{
            $mobile_goto_url=urldecode($mobile_goto_url);
        }
        $uni_code=WeixinLogin::getInstance()->getUniCode();
        $this->uni_code=$uni_code;

        $this->goto_url=$goto_url;

        // 手机端应访问的地址
        $this->scan_url=U('mobile',['uni_code'=>$uni_code],true,true).'?goto_url='.urlencode($mobile_goto_url);

        // PC端轮询地址
        $this->check_url=U('checkPcLogin',['uni_code'=>$uni_code],true,true);
        $this->display($this->_template);
    }

    public function qrcode($url){
        header('Content-type:image/png');
        $options = new QROptions([
            'scale'     => 50,
            'imageBase64'=>false
        ]);
        echo (new QRcode($options))->render(urldecode($url));
    }

    public function mobile($uni_code,$goto_url){
        $wx_info=WeixinLogin::getInstance()->getInfoForMobile();
        if ($wx_info){
            WeixinLogin::getInstance()->notify($uni_code,$wx_info);
            redirect(urldecode($goto_url));
        }
    }

    public function checkPcLogin($uni_code){
        $info=WeixinLogin::getInstance()->getNotifyInfo($uni_code);
        if (!$info){
            // uni_code过期，需要刷新页面
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'need_refresh'
            ]);
        }
        if ($info==='no_scan'){
            // 未扫码或通知PC端
            $this->ajaxReturn([
                'status'=>2,
                'info'=>'no_scan'
            ]);
        }

        session(C('WX_INFO_SESSION_KEY',null,'wx_info'),json_encode($info));

        $this->ajaxReturn([
            'status'=>1,
            'info'=>$info
        ]);
    }
}