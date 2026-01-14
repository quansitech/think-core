<?php


namespace Qscmf\Lib;


use EasyWeChat\OfficialAccount\Application;
use Qscmf\Exception\TestingException;

class WeixinLogin
{
    private static $_self;
    private $_easy_wechat_app;
    private static $_timeout=300;
    private $_session_key;

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
        $this->_easy_wechat_app=new Application($config);
        $this->_session_key=C('WX_INFO_SESSION_KEY',null,'wx_info');
    }

    public function getInfoForMobile(){
        if (session('?'.$this->_session_key)){
            return json_decode(session($this->_session_key),true);
        }

        $code = I('get.code');
        if ($code && $wx_info=$this->_easy_wechat_app->getOauth()->userFromCode($code)){
            session($this->_session_key,json_encode($wx_info->toArray()));
            redirect(session('cur_request_url'));
            qs_exit('');
        }

        $url=HTTP_PROTOCOL.'://'.SITE_URL.$_SERVER[C('URL_REQUEST_URI')];
        session('cur_request_url',$url);

        $redirect_url = $this->_easy_wechat_app->getOauth()->scopes(['snsapi_userinfo'])
            ->redirect($url);

        header("Location: {$redirect_url}");
        qs_exit('');
    }

    public function getNotifyInfo($uni_code){
        if ($info=S('wx_login'.$uni_code)){
            if ($info==='no_scan'){
                return $info;
            }
            S('wx_login'.$uni_code,null);
            return is_json($info) ? json_decode($info,true) : $info;
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