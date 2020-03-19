<?php
if(!function_exists('normalizeRelativePath')) {
    /**
     * Normalize relative directories in a path.
     *
     * @param string $path
     *
     * @return string
     * @throws Exception
     *
     */
    function normalizeRelativePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = removeFunkyWhiteSpace($path);

        $parts = [];

        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;

                case '..':
                    if (empty($parts)) {
                        throw new Exception(
                            'Path is outside of the defined root, path: [' . $path . ']'
                        );
                    }
                    array_pop($parts);
                    break;

                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }
}

if(!function_exists('removeFunkyWhiteSpace')) {
    /**
     * Removes unprintable characters and invalid unicode characters.
     *
     * @param string $path
     *
     * @return string $path
     */
    function removeFunkyWhiteSpace($path)
    {
        // We do this check in a loop, since removing invalid unicode characters
        // can lead to new characters being created.
        while (preg_match('#\p{C}+|^\./#u', $path)) {
            $path = preg_replace('#\p{C}+|^\./#u', '', $path);
        }

        return $path;
    }
}

if(!function_exists('getRelativePath')){
    /**
     * 计算$b相对于$a的相对路径
     * @param string $a
     * @param string $b
     * @return string
     */
    function getRelativePath($a, $b) {
        $relativePath = "";
        $pathA = explode('/', $a);
        $pathB = explode('/', dirname($b));
        $n = 0;
        $len = count($pathB) > count($pathA) ? count($pathA) : count($pathB);
        do {
            if ( $n >= $len || $pathA[$n] != $pathB[$n] ) {
                break;
            }
        } while (++$n);
        $relativePath .= str_repeat('../', count($pathB) - $n);
        $relativePath .= implode('/', array_splice($pathA, $n));
        return $relativePath;
    }
}

// 清空INJECT_RBAC标识key的session值
if(!function_exists('cleanRbacKey')){
    function cleanRbacKey(){
        $inject_rbac_arr = C('INJECT_RBAC');
        if (empty($inject_rbac_arr)){
            return true;
        }

        $keys = array_column($inject_rbac_arr, 'key');
        array_map(function ($str){
            session($str, null);
        }, $keys);
    }
}

if(!function_exists('base64_url_encode')){
    function base64_url_encode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }
}


if(!function_exists('base64_url_decode')){
    function base64_url_decode($str){
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }
}

//拼接imageproxy的图片地址
if(!function_exists('imageproxy')){
    function imageproxy($options, $file_id, $cache = ''){
        if(filter_var($file_id, FILTER_VALIDATE_URL)){
            $path = $file_id;
            $uri = $file_id;
        }else{
            $file_pic_model = M('FilePic');
            if($cache){
                $file_pic_model->cache($cache);
            }
            $file_ent = $file_pic_model->find($file_id);
            $file_path = UPLOAD_PATH . '/' . $file_ent['file'];
            $path = $file_ent['file'] ? ltrim($file_path, '/') : $file_ent['url'];
            $uri = $file_ent['file'] ? HTTP_PROTOCOL .  '://' . DOMAIN . $file_path : $file_ent['url'];
        }

        $format = env('IMAGEPROXY_URL');
        $format = str_replace("{schema}", HTTP_PROTOCOL, $format);
        $format = str_replace("{domain}", SITE_URL, $format);
        $format = str_replace("{options}", $options, $format);
        $format = str_replace("{path}", $path, $format);
        $format = str_replace("{remote_uri}", $uri, $format);

        return $format;
    }
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
if(!function_exists('list_to_tree')) {
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}

//将从list_to_tree转换成的tree转换成树状结构下来列表
if(!function_exists('genSelectByTree')) {
    function genSelectByTree($tree, $child = '_child', $level = 0)
    {
        $select = array();
        foreach ($tree as $key => $data) {
            if (isset($data[$child])) {
                $data['level'] = $level;
                $select[] = $data;
                $child_list = genSelectByTree($data[$child], $child, $level + 1);
                foreach ($child_list as $k => $v) {
                    $select[] = $v;
                }
            } else {
                $data['level'] = $level;
                $select[] = $data;
            }
        }
        return $select;
    }
}

if(!function_exists('isAdminLogin')) {
    function isAdminLogin()
    {
        return session('?' . C('USER_AUTH_KEY')) && session('?ADMIN_LOGIN');
    }
}

if(!function_exists('qs_exit')){
    function qs_exit($content = ''){
        if(env('APP_ENV') == 'testing'){
            throw new \Qscmf\Exception\TestingException($content);
        }
        else{
            exit($content);
        }
    }
}

if(!function_exists('asset')){
    function asset($path){
        $config = C('ASSET');
        return $config['prefix'] . $path;
    }
}

if(!function_exists('old')) {
    function old($key, $default = null)
    {
        return \Qscmf\Core\Flash::get('qs_old_input.' . $key, $default);
    }
}

if(!function_exists('flashError')) {
    function flashError($err_msg)
    {
        \Qscmf\Core\FlashError::set($err_msg);
    }
}

if(!function_exists('verifyAuthNode')) {
    function verifyAuthNode($node)
    {
        list($module_name, $controller_name, $action_name) = explode('.', $node);
        return \Qscmf\Core\QsRbac::AccessDecision($module_name, $controller_name, $action_name) ? 1 : 0;
    }
}

/**
 * 配合文件上传插件使用  把file_ids转化为srcjson
 * example: $ids = '1,2'
 *   return: [ "https:\/\/csh-pub-resp.oss-cn-shenzhen.aliyuncs.com\/Uploads\/image\/20181123\/5bf79e7860393.jpg",
 *          //有数据的时候返回showFileUrl($id)的结果
 *    ''    //没有数据时返回空字符串
 *   ];
 * @param $ids array|string file_ids
 * @return string data srcjson
 */
if(!function_exists('fidToSrcjson')) {
    function fidToSrcjson($ids)
    {
        if ($ids) {
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            $json = [];
            foreach ($ids as $id) {
                $json[] = showFileUrl($id);
            }
            return htmlentities(json_encode($json));
        } else {
            return '';
        }
    }
}


/**
 * 裁剪字符串
 *   保证每个裁切的字符串视觉长度一致,而curLength裁剪会导致视觉长度参差不齐
 *   frontCutLength: 中文算2个字符长度，其他算1个长度
 *   curLength:      每个字符都是算一个长度
 *
 *   example1: 若字符串长度小等于$len,将会原样输出$str;
 *   frontCutLength('字符1',5)；    @return: '字符1';
 *
 *   example2: 若字符串长度大于$len
 *   frontCutLength('字符12',5)；   @return: '字...';(最后的"..."会算入$len)
 *
 *   example3: 若字符串长度大于$len，且最大长度的字符不能完整输出,则最大长度的字符会被忽略
 *   frontCutLength('1字符串',5)；  @return: '1....';("字"被省略，最后的"..."会算入$len)
 *
 * @param $str string 要截的字符串
 * @param $len int|string 裁剪的长度 按英文的长度计算
 * @return false|string
 */
if(!function_exists('frontCutLength')) {
    function frontCutLength($str, $len)
    {
        $gbStr = iconv('UTF-8', 'GBK', $str);
        $count = strlen($gbStr);
        if ($count <= $len) {
            return $str;
        }
        $gbStr = mb_strcut($gbStr, 0, $len - 3, 'GBK');

        $str = iconv('GBK', 'UTF-8', $gbStr);
        return $str . '...';
    }
}


//展示数据库存储文件URL地址
if(!function_exists('showFileUrl')){
    function showFileUrl($file_id, $default_file = ''){
        if(filter_var($file_id, FILTER_VALIDATE_URL)){
            return $file_id;
        }

        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->cache(true, 86400)->find();

        if(!$file_pic_ent || ($file_pic_ent['url'] == '' && $file_pic_ent['file'] == '')){
            return $default_file;
        }

        //如果图片是网络链接，直接返回网络链接
        if(!empty($file_pic_ent['url']) && $file_pic_ent['security'] != 1){
            return $file_pic_ent['url'];
        }

        if($file_pic_ent['security'] == 1){
            //alioss
            if(!empty($file_pic_ent['url'])){
                $ali_oss = new \Common\Util\AliOss();
                $config = C('UPLOAD_TYPE_' . strtoupper($file_pic_ent['cate']));
                $object = trim(str_replace($config['oss_host'], '', $file_pic_ent['url']), '/');
                $url = $ali_oss->getOssClient($file_pic_ent['cate'])->signUrl($object, 60);
                return $url;
            }

            if(strtolower(MODULE_NAME) == 'admin' || $file_pic_ent['owner'] == session(C('USER_AUTH_KEY'))){

                session('file_auth_key', $file_pic_ent['owner']);
                return U('/api/upload/load', array('file_id' => $file_id));
            }
        }
        else{
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