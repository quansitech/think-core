<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
/**
 * 系统行为扩展：表单令牌生成
 */
class TokenBuildBehavior {

    public function run(&$content){
        if(C('TOKEN_ON')) {
            list($tokenName,$tokenKey,$tokenValue)=$this->getToken();
            $input_token = '<input type="hidden" name="'.$tokenName.'" value="'.$tokenKey.'_'.$tokenValue.'" />';
            $meta_token = '<meta name="'.$tokenName.'" content="'.$tokenKey.'_'.$tokenValue.'" />';
            if(strpos($content,'{__TOKEN__}')) {
                // 指定表单令牌隐藏域位置
                $content = str_replace('{__TOKEN__}',$input_token,$content);
            }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {
                // 智能生成表单令牌隐藏域
                $content = str_replace($match[0],$input_token.$match[0],$content);
            }
            $content = str_ireplace('</head>',$meta_token.'</head>',$content);
        }else{
            $content = str_replace('{__TOKEN__}','',$content);
        }
    }

    //获得token
    private function getToken(){
        return getToken();
    }
}