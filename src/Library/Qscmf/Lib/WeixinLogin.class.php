<?php


namespace Qscmf\Lib;


use EasyWeChat\Factory;

class WeixinLogin
{
    private static $_self;
    private $_easy_wechat_app;
    private static $_timeout=300;

    /**
     * @return self
     */
    public static function getInstance(){
        if (self::$_self){
            return self::$_self;
        }
        self::$_self=new self();
        return self::getInstance();
    }

    private function __construct()
    {
        $config=[
            'app_id'=>env('WX_APPID',''),
            'secret'=>env('WX_APPSECRET','')
        ];
        $this->_easy_wechat_app=Factory::officialAccount($config);
    }

    public function getInfoForMobile($uni_code=''){
        if (session('?wx_info')){
            return json_decode(session('wx_info'),true);
        }

        if (I('get.code') && $wx_info=$this->_easy_wechat_app->oauth->user()){
            session('wx_info',$wx_info->toJSON());
            return $wx_info->toArray();
        }

        $url=HTTP_PROTOCOL.'://'.SITE_URL.$_SERVER[C('URL_REQUEST_URI')];
        if (strpos($url,'?')===false){
            $url.='?';
        }
        if ($uni_code){
            $url.='&uni_code='.$uni_code;
        }

        $response = $this->_easy_wechat_app->oauth->scopes(['snsapi_userinfo'])
            ->redirect($url);

        $response->send();
    }

    public function getNotifyInfo($uni_code){
        if ($info=S('wx_login'.$uni_code)){
            if ($info==='no_scan'){
                return $info;
            }
            S('wx_login'.$uni_code,null);
            return json_decode($info,true);
        }else{
            return false;
        }
    }

    public function notify($uni_code,$wx_info){
        S('wx_login'.$uni_code,json_encode($wx_info),self::$_timeout);
    }

    public function getUniCode(){
        $uni_code=md5(time()).rand(1000,9999);
        if (S('wx_login'.$uni_code)){
            return $this->getUniCode();
        }
        S('wx_login'.$uni_code,'no_scan',self::$_timeout);
        return $uni_code;
    }

}