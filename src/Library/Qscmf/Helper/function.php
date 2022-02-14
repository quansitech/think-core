<?php
if(!function_exists('is_json')){
    function is_json($string)
    {
        if(!is_string($string)){
            return false;
        }

        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('filterItemsByAuthNode')){
    function filterItemsByAuthNode($check_items){
        return array_values(array_filter(array_map(function ($items){
            return filterOneItemByAuthNode($items, $items['auth_node']);
        }, $check_items)));
    }
}

if (!function_exists("filterOneItemByAuthNode")){
    function filterOneItemByAuthNode($item, $item_auth_node = null){
        if ($item_auth_node){
            $auth_node = (array)$item_auth_node;
            $node = $auth_node['node'] ? (array)$auth_node['node'] : $auth_node;
            $logic = $auth_node['logic'] ? $auth_node['logic'] : 'and';

            switch ($logic){
                case 'and':
                    foreach ($node as $v){
                        $has_auth = verifyAuthNode($v);
                        if (!$has_auth){
                            $item = null;
                            break;
                        }
                    }
                    break;
                case 'or':
                    $false_count = 0;
                    foreach ($node as $v){
                        $has_auth = verifyAuthNode($v);
                        if ($has_auth){
                            break;
                        }else{
                            $false_count ++;
                        }
                    }
                    if ($false_count == count($node)){
                        $item = null;
                    }
                    break;
                default:
                    E('Invalid logic value');
                    break;
            }
        }

        return $item;
    }
}

if(!function_exists('uniquePageData')){
    function uniquePageData($cache_key, $unique_key, $page, $data){
        $current_cache_key = $cache_key . '_' . $page;
        $pre_cache_key = $cache_key . '_' . ($page -1);
        if($page == 1 || !session($pre_cache_key)){
            $key_data = collect($data)->map(function($item) use ($unique_key){
                return [
                    $unique_key => $item[$unique_key]
                ];
            })->all();
            session($current_cache_key, $key_data);
            return $data;
        }

        $pre_data = session($pre_cache_key);
        $res = collect($data)->filter(function($item) use ($pre_data, $unique_key) {
            $res = collect($pre_data)->where($unique_key, $item[$unique_key])->all();
            return $res ? false : true;
        });
        return $res->all();
    }
}

if(!function_exists('checkGt')){
    function checkGt($value, $gt_value){
        if(!is_numeric($value)){
            return null;
        }
        return $value > $gt_value;
    }
}

if(!function_exists('convert')){
    function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}


if(!function_exists('isUrl')){
    function isUrl($url){
        if($url == ''){
            return false;
        }

        $validator = \Symfony\Component\Validator\Validation::createValidator();
        $validations = $validator->validate($url, [ new \Symfony\Component\Validator\Constraints\Url()]);
        return count($validations) > 0 ? false : true;
    }
}
/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
if(!function_exists('time_format')) {
    function time_format($time = NULL, $format = 'Y-m-d H:i:s')
    {
        $time = $time === NULL ? NOW_TIME : intval($time);
        return date($format, $time);
    }
}

if(!function_exists('qsEmpty')){
    function qsEmpty($value, $except_zero = true){
        if(is_string($value)){
            $value = trim($value);
        }

        if(is_object($value)){
            $value = (array)$value;
        }

        if(!$except_zero){
            return empty($value);
        }

        if($value !== 0 && $value !== "0" && empty($value)){
            return true;
        }
        else{
            return false;
        }
    }
}

if(!function_exists('testing_throw')){
    function testing_throw($e)
    {
        if($e instanceof \Qscmf\Exception\TestingException){
            throw new \Qscmf\Exception\TestingException($e->getMessage());
        }
    }
}

if(!function_exists('isTesting')){
    function isTesting()
    {
        return env('APP_ENV') == 'testing' && !isset($_SERVER['DUSK_TEST']);
    }
}

if(!function_exists('readerSiteConfig')) {
    function readerSiteConfig()
    {
        if(!class_exists('\Common\Model\ConfigModel')){
            E('\Common\Model\ConfigModel not found');
        }
        $config = new \Common\Model\ConfigModel();

        $site_config = S('DB_CONFIG_DATA');

        if (!$site_config) {
            $site_config = $config->lists();
            S('DB_CONFIG_DATA', $site_config);
        }
        C($site_config); //添加配置
    }
}

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
                        $arr = explode('/', realpath('.'));
                        array_walk($arr, function($item, $key) use(&$parts){
                            if($item){
                                $parts[] = $item;
                            }
                        });
                    }
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
    /**
     * 拼接imageproxy的图片地址
     * @deprecated 在v12版本后移出核心， 请使用 https://github.com/quansitech/qscmf-utils 的 Common::imageproxy 代替
     */
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
        $remote = env("IMAGEPROXY_REMOTE");
        if($remote){
            $remote_parse = parse_url($remote);
            $schema = $remote_parse['scheme'];
            $domain = $remote_parse['host'];
        }
        else{
            $schema = HTTP_PROTOCOL;
            $domain = SITE_URL;
        }
        $format = str_replace("{schema}", $schema, $format);
        $format = str_replace("{domain}", $domain, $format);
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
        if(isTesting()){
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

// 根据地区id获取其全名称
if(!function_exists('getAreaFullCname1ByID')) {
    function getAreaFullCname1ByID($id, $model = 'AreaV',$field = 'full_cname1')
    {
        return M($model)->where(['id' => $id])->getField($field);
    }
}

// 根据多个地区id获取其下属的所有地区
if (!function_exists('getAllAreaIdsWithMultiPids')){
    function getAllAreaIdsWithMultiPids($city_ids, $model = 'AreaV', $max_level = 3, $need_exist = true, $cache = ''){
        $kname = 'kname';
        $value = 'value';
        $kname_column_mapping = ['country_id','p_id','c_id','d_id'];
        $i = 0;
        $kname_column = 'case';
        while ($i <= $max_level){
            $kname_column .= " when level = ".$i." then '".$kname_column_mapping[$i]."' ";
            $i++;
        }
        $kname_column .= 'end as '.$kname;

        if(is_int($city_ids)){
            $city_ids = (string)$city_ids;
        }
        if (is_string($city_ids)){
            $city_ids = explode(',', $city_ids);
        }
        $all_city_ids = [];
        if (!empty($city_ids)){
            $cls_name = 'Common\Model\\'.$model.'Model';
            $model_class = new $cls_name();
            $all_ids_model_class = new $cls_name();

            $is_array_cache = is_array($cache);
            if ($cache && !$is_array_cache){
                $model_class = $model_class->cache($cache);
                $all_ids_model_class = $all_ids_model_class->cache($cache);
            }
            if ($is_array_cache){
                list($key, $expire, $type) = $cache;
                $model_class = $model_class->cache($key, $expire, $type);
                $all_ids_model_class = $all_ids_model_class->cache($key, $expire, $type);
            }

            $map['id'] = ['IN', $city_ids];
            $map['level'] = ['ELT', $max_level];
            $field = "{$kname_column},group_concat(id) as ".$value;
            $list = $model_class->notOptionsFilter()->where($map)->group('level')->getField($field, true);

            if ($list){
                $full_map_arr = collect($list)->map(function ($value, $kname){
                    $value_str = "'".implode("','",explode(",", $value))."'";
                    return "{$kname} in (".$value_str.")";
                })->all();

                $full_map = implode(' or ', array_values($full_map_arr));
                $all_city_ids = $all_ids_model_class->notOptionsFilter()->where($full_map)->getField('id', true);
            }
            !$need_exist && $all_city_ids = array_values(array_unique(array_merge($city_ids, $all_city_ids)));
        }

        return $all_city_ids;
    }

    // 递归删除目录下的空目录
    // $preserve 是否保留本身目录，默认为false，不保留
    if(!function_exists('deleteEmptyDirectory')) {
        function deleteEmptyDirectory($directory, $preserve = false)
        {
            if (!is_dir($directory)) {
                return false;
            }

            $items = new \FilesystemIterator($directory);

            foreach ($items as $item) {
                if ($item->isDir() && !$item->isLink()) {
                    deleteEmptyDirectory($item->getPathname());
                }
            }

            if (!$preserve && count(scandir($directory)) === 2) {
                rmdir($directory);
            }

            return true;
        }
    }


}