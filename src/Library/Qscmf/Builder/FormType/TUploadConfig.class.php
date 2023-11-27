<?php

namespace Qscmf\Builder\FormType;

trait TUploadConfig
{

    protected function genUploadConfigCls($form_extra, $def_type){
        $type = $this->getType($form_extra, $def_type);
        return new UploadConfig($type);
    }

    protected function getType($form_extra, $def_type){
        if (!$form_extra){
            return $def_type;
        }
        $regex = '/(data-url)(\s*=\s*[\"|\'])(\S*)([\"|\'])/';
        $r = preg_match($regex, $form_extra, $matches);
        if (!$r){
            return $def_type;
        }
        $url = $matches[3];
        $type = $this->getParamCate($url);
        return $type ?? $def_type;
    }

    protected function getMethod($url){
        $url = urldecode($url);
        isUrl($url) && $url = str_replace(HTTP_PROTOCOL."://".SITE_URL,'',$url);

        $sub_root = str_replace('-', '\-',trim(__ROOT__, '/'));
        $pattern[0] = "/^(\/$sub_root\/)/";
        $pattern[1] = "/^\s+|\s+$/";
        $pattern[2] = "/^\//";

        $url = preg_replace($pattern, '', $url);
        $spe_index = strpos($url, '?');
        if ($spe_index !== false){
            $url_arr = parse_url($url);
            $path = $url_arr['path'];
        }else{
            $path = $url;
        }

        $ext_index = strpos($path, '.');
        $ext_index !== false && $path = substr($path, 0, strpos($path, '.'));
        $path_arr = explode("/", $path);
        return $path_arr[2];
    }

    protected function getParamCate($url):?string {
        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            if (isset($query['cate'])) {
                return $query['cate'];
            }
        }

        // 检测并提取path部分的cate参数
        $pattern = "/cate\/([^\/?]+)(\/|$|\?)/";
        if (preg_match($pattern, $parts['path'], $matches)) {
            // 忽略文件扩展名
            return preg_replace('/\.[^.]+$/', '', $matches[1]);;
        }

        return null;
    }

}