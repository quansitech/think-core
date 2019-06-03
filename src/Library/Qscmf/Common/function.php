<?php
if(!function_exists('old')){
    function old($key, $default = null){
        return \Qscmf\Lib\Flash::get('qs_old_input.' . $key, $default);
    }
}

if(!function_exists('flashError')){
    function flashError($err_msg){
        \Qscmf\Lib\FlashError::set($err_msg);
    }
}

if(!function_exists('verifyAuthNode')) {
    function verifyAuthNode($node)
    {
        list($module_name, $controller_name, $action_name) = explode('.', $node);
        return \Qscmf\Lib\GyRbac::AccessDecision($module_name, $controller_name, $action_name) ? 1 : 0;
    }
}

if(!function_exists('asset')) {
    function asset($path)
    {
        $config = C('ASSET');
        return $config['prefix'] . $path;
    }
}

if(!function_exists('addon_t')) {
    function addon_t($addon_name, $file)
    {
        $url = APP_PATH . 'Addons/' . ucfirst($addon_name) . '/View/';
        $url .= C('DEFAULT_THEME') ? C('DEFAULT_THEME') . '/' : '';
        $url .= $file . C('TMPL_TEMPLATE_SUFFIX');
        return $url;
    }
}

if(!function_exists('isAdminLogin')) {
    function isAdminLogin()
    {
        return session('?' . C('USER_AUTH_KEY')) && session('?ADMIN_LOGIN');
    }
}

//登录出错处理 一般与isShowVerify 一起使用实现错误登录次数过多采用验证码的功能
if(!function_exists('loginFail')) {
    function loginFail()
    {
        if (session('?login_fail_times')) {
            $login_fail_times = session('login_fail_times');
            $login_fail_times++;
            session('login_fail_times', $login_fail_times);
        } else {
            session('login_fail_times', 1);
        }
    }
}

//是否显示验证码 一般与loginFail 一起使用实现错误登录次数过多采用验证码的功能
if(!function_exists('isShowVerify')) {
    function isShowVerify()
    {
        if (!session('?login_fail_times')) {
            return false;
        }

        if (session('login_fail_times') >= C('LOGIN_ERROR_TIMES', null, 3)) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('getModuleName')) {
    function getModuleName()
    {
        $map['name'] = MODULE_NAME;
        $map['level'] = 1;
        $title = D('node')->where($map)->getField('title');
        return $title ? $title : MODULE_NAME;
    }
}

if(!function_exists('getModuleId')) {
    function getModuleId()
    {
        $map['name'] = MODULE_NAME;
        $map['level'] = 1;
        $id = D('node')->where($map)->getField('id');
        return $id;
    }
}

if(!function_exists('getControllerName')) {
    function getControllerName()
    {
        $map['name'] = CONTROLLER_NAME;
        $map['level'] = 2;
        $map['pid'] = getModuleId();
        $title = D('node')->where($map)->getField('title');
        return $title ? $title : CONTROLLER_NAME;
    }
}

if(!function_exists('getControllerId')) {
    function getControllerId()
    {
        $map['name'] = CONTROLLER_NAME;
        $map['level'] = 2;
        $map['pid'] = getModuleId();
        $id = D('node')->where($map)->getField('id');
        return $id;
    }
}

if(!function_exists('getActionName')) {
    function getActionName()
    {
        $map['name'] = ACTION_NAME;
        $map['level'] = 3;
        $map['pid'] = getControllerId();
        $title = D('node')->where($map)->getField('title');
        return $title ? $title : ACTION_NAME;
    }
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
if(!function_exists('parse_config_attr')) {
    function parse_config_attr($string)
    {
        $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
        if (strpos($string, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

//展示数据库存储文件URL地址
if(!function_exists('showFileUrl')) {
    function showFileUrl($file_id)
    {
        if (filter_var($file_id, FILTER_VALIDATE_URL)) {
            return $file_id;
        }

        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();

        if (!$file_pic_ent) {
            return '';
        }

        //如果图片是网络链接，直接返回网络链接
        if (!empty($file_pic_ent['url']) && $file_pic_ent['security'] != 1) {
            return $file_pic_ent['url'];
        }

        if ($file_pic_ent['security'] == 1) {
            //alioss
            if (!empty($file_pic_ent['url'])) {
                $ali_oss = new \Common\Util\AliOss();
                $config = C('UPLOAD_TYPE_' . strtoupper($file_pic_ent['cate']));
                $object = trim(str_replace($config['oss_host'], '', $file_pic_ent['url']), '/');
                $url = $ali_oss->getOssClient($file_pic_ent['cate'])->signUrl($object, 60);
                return $url;
            }

            if (strtolower(MODULE_NAME) == 'admin' || $file_pic_ent['owner'] == session(C('USER_AUTH_KEY'))) {

                session('file_auth_key', $file_pic_ent['owner']);
                return U('/api/upload/load', array('file_id' => $file_id));
            }
        } else {
            return UPLOAD_PATH . '/' . $file_pic_ent['file'];
        }
    }
}

if(!function_exists('getAutocropConfig')) {
    function getAutocropConfig($key)
    {
        $ent = D('Addons')->where(['name' => 'AutoCrop', 'status' => 1])->find();
        $config = json_decode($ent['config'], true);
        $config = json_decode(html_entity_decode($config['config']), true);
        return $config[$key];
    }
}

//取缩略图
if(!function_exists('showThumbUrl')) {
    function showThumbUrl($file_id, $prefix, $replace_img = '')
    {
        if (filter_var($file_id, FILTER_VALIDATE_URL)) {
            return $file_id;
        }

        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
        //自动填充的测试数据处理
        if ($file_pic_ent['seed'] && $file_pic_ent['url'] && ($config = getAutocropConfig($prefix))) {
            $width = $config[0];
            $high = $config[1];

            return preg_replace('/(http[s]?\:\/\/[a-z0-9\-\.\_]+?)\/(\d+?)\/(\d+)(.*)/i', "$1/{$width}/{$high}$4", $file_pic_ent['url']);
        }

        if (!$file_pic_ent && !$replace_img) {
            //不存在图片时，显示默认封面图
            $file_pic_ent = $file_pic->where(array('id' => C('DEFAULT_THUMB')))->find();
        }
        $file_name = basename(UPLOAD_DIR . '/' . $file_pic_ent['file']);
        $thumb_path = UPLOAD_DIR . '/' . str_replace($file_name, $prefix . '_' . $file_name, $file_pic_ent['file']);
        //当file字段不存在值时，程序编程检测文件夹是否存在，依然会通过。因此要加上当file字段有值这项条件
        if (file_exists($thumb_path) === true && !empty($file_pic_ent['file'])) {

            return UPLOAD_PATH . '/' . str_replace($file_name, $prefix . '_' . $file_name, $file_pic_ent['file']);
        } elseif ($replace_img) {
            return $replace_img;
        } else {
            return showFileUrl($file_id);
        }
    }
}

//展示数据库存储文件物理路径
if(!function_exists('showFilePath')) {
    function showFilePath($file_id)
    {
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->find($file_id);
        if ($file_pic_ent) {
            return UPLOAD_DIR . DIRECTORY_SEPARATOR . $file_pic_ent['file'];
        }
        return '';
    }
}

if(!function_exists('showHtmlContent')) {
    function showHtmlContent($content)
    {
        return html_entity_decode($content);
    }
}

//截取内容的长度
if(!function_exists('cutLength')) {
    function cutLength($content, $len)
    {
        if (mb_strlen($content, 'utf-8') <= $len) {
            return $content;
        } else {
            return mb_substr($content, 0, $len, 'utf-8') . '......';
        }
    }
}

if(!function_exists('readerSiteConfig')) {
    function readerSiteConfig()
    {
        $site_config = S('DB_CONFIG_DATA');

        if (!$site_config) {
            $site_config = D('Config')->lists();
            S('DB_CONFIG_DATA', $site_config);
        }
        C($site_config); //添加配置
    }
}