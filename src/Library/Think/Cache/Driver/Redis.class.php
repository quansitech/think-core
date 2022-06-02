<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;
defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache {
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPORT_').':redis');
        }
        $options = array_merge(array (
            'host'          => C('REDIS_HOST') ? : '127.0.0.1',
            'port'          => C('REDIS_PORT') ? : 6379,
            'password'   => C('REDIS_PASSWORD') ?: '',
            'timeout'       => C('DATA_CACHE_TIMEOUT') ? : false,
            'persistent'    => false,
        ),$options);

        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        if ('' != $options['password']) {
            $this->handler->auth($options['password']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @param string $flag
     *
     * @return boolean
     */
    public function set($name, $value, $expire = null, $flag = '') {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if($flag !== '') {
            $option = $expire ? [$flag, 'ex' => $expire] : [$flag];
            $result = $this->handler->set($name, $value, $option);
        }else if(is_integer($expire) && $expire > 0){
            $result = $this->handler->set($name, $value, $expire);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    public function ttl($name){
        $name   =   $this->options['prefix'].$name;
        return $this->handler->ttl($name);
    }

    public function decr(string $name){
        return $this->handler->decr($this->options['prefix'] . $name);
    }

    public function incr(string $name){
        return $this->handler->incr($this->options['prefix'] . $name);
    }

    public function sAdd(string $name, ...$value){
        return $this->handler->sAdd($this->options['prefix'] . $name, ...$value);
    }

    public function hSetNx(string $name, string $hashKey, string $value){
        return $this->handler->hSetNx($this->options['prefix'] . $name, $hashKey, $value);
    }

    public function sRandMember(string $name, int $count = 1){
        return $this->handler->sRandMember($this->options['prefix'] . $name, $count);
    }

    public function hGet(string $name, string $hashKey) {
        return $this->handler->hGet($this->options['prefix'] . $name, $hashKey);
    }

    public function hLen(string $name) {
        return $this->handler->hLen($this->options['prefix'] . $name);
    }

    public function sCard(string $name) {
        return $this->handler->sCard($this->options['prefix'] . $name);
    }

    public function sRem(string $name, ...$member1) {
        return $this->handler->sRem($this->options['prefix'] . $name, ...$member1);
    }

    public function hDel(string $name, string $hashKey1, ...$otherHashKeys) {
        return $this->handler->hDel($this->options['prefix'] . $name, $hashKey1, ...$otherHashKeys);
    }

    public function del(string $key1, ...$otherKeys){
        return $this->handler->del($this->options['prefix'] . $key1, ...$otherKeys);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return
     * @deprecated
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }

}
