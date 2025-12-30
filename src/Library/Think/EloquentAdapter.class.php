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

namespace Think;

use Illuminate\Database\Capsule\Manager as CapsuleManager;

/**
 * Eloquent适配器类
 * 用于封装Eloquent相关操作，保持Model类的简洁性
 */
class EloquentAdapter
{
    // 数据库连接
    protected $connection = null;
    protected $connectionName = null;
    
    // 调试相关属性
    protected $queryStr = '';
    protected $modelSql = array();
    protected $queryTimes = 0;
    protected $executeTimes = 0;
    protected $lastInsID = null;
    protected $numRows = 0;
    protected $error = '';

    /**
     * Laravel Capsule Manager 实例
     * @var \Illuminate\Database\Capsule\Manager|null
     */
    protected static $capsule = null;
    
    // 配置
    protected $config = [];
    
    /**
     * 构造函数
     * @param array $config 数据库配置
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    protected static function getCapsule() {
        if (self::$capsule === null && class_exists('Illuminate\Database\Capsule\Manager')) {
            self::$capsule = new CapsuleManager;
        }
        return self::$capsule;
    }
    
    /**
     * 获取数据库连接
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $capsule = self::getCapsule();
            if (!$capsule) {
                throw new \RuntimeException('Laravel Capsule Manager 不可用，请确保已安装 illuminate/database 组件');
            }
            
            $connectionName = $this->getConnectionName();
            $this->connection = $capsule->getConnection($connectionName);
        }
        
        return $this->connection;
    }
    
    /**
     * 获取连接名称
     * @return string
     */
    protected function getConnectionName()
    {
        if (!$this->connectionName) {
            // 生成唯一的连接名称，基于数据库配置
            $this->connectionName = 'think_' . md5(serialize($this->config));
            
            // 确保连接已添加到Capsule
            $capsule = self::getCapsule();
            
            if ($capsule) {
                $laravelConfig = $this->convertToLaravelConfig($this->config);
            
                
                // 添加特定连接
                $capsule->addConnection($laravelConfig, $this->connectionName);
            }
        }
        
        return $this->connectionName;
    }
    
    /**
     * 将ThinkPHP数据库配置转换为Laravel配置格式
     * 参考Laravel的config/database.php配置结构进行优化
     * @param array $config ThinkPHP配置
     * @return array Laravel配置
     */
    protected function convertToLaravelConfig($config)
    {
        // 首先规范化配置，确保所有必要的字段都有值
        $normalizedConfig = $this->normalizeDbConfig($config);
        
        $typeMap = [
            'mysql' => 'mysql',
            'mysqli' => 'mysql',
            'mariadb' => 'mariadb',
            'pgsql' => 'pgsql',
            'sqlite' => 'sqlite',
            'sqlsrv' => 'sqlsrv',
        ];
        
        $driver = isset($typeMap[$normalizedConfig['type']]) ? $typeMap[$normalizedConfig['type']] : 'mysql';
        
        // 基础配置
        $laravelConfig = [
            'driver' => $driver,
            'host' => $normalizedConfig['hostname'] ?? '127.0.0.1',
            'port' => $normalizedConfig['hostport'] ?? $this->getDefaultPort($driver),
            'database' => $normalizedConfig['database'] ?? '',
            'username' => $normalizedConfig['username'] ?? '',
            'password' => $normalizedConfig['password'] ?? '',
            'charset' => $normalizedConfig['charset'] ?? $this->getDefaultCharset($driver),
            'prefix' => '', // ThinkPHP已经处理了前缀，所以这里设置为空
            'strict' => $normalizedConfig['strict'] ?? true,
            'engine' => null,
        ];
        
        // 根据数据库类型添加特定配置
        $laravelConfig = array_merge($laravelConfig, $this->getDriverSpecificConfig($driver, $normalizedConfig));
        
        return $laravelConfig;
    }
    
    /**
     * 规范化数据库配置，确保所有必要的字段都有值
     * @param array $config 原始配置
     * @return array 规范化后的配置
     */
    protected function normalizeDbConfig($config)
    {
        if (!is_array($config)) {
            $config = [];
        }
        
        // 确保type字段存在
        if (empty($config['type'])) {
            $config['type'] = C('DB_TYPE', null, 'mysql');
        }
        
        // 确保hostname有默认值
        if (empty($config['hostname'])) {
            $config['hostname'] = C('DB_HOST', null, '127.0.0.1');
        }
        
        // 确保database有默认值
        if (empty($config['database'])) {
            $config['database'] = C('DB_NAME', null, '');
        }
        
        // 确保username有默认值
        if (empty($config['username'])) {
            $config['username'] = C('DB_USER', null, '');
        }
        
        // 确保password有默认值
        if (!isset($config['password'])) {
            $config['password'] = C('DB_PASSWORD', null, '');
        }
        
        // 确保hostport有默认值
        if (empty($config['hostport'])) {
            $config['hostport'] = C('DB_PORT', null, $this->getDefaultPort($config['type']));
        }
        
        // 确保charset有默认值
        if (empty($config['charset'])) {
            $config['charset'] = C('DB_CHARSET', null, $this->getDefaultCharset($config['type']));
        }
        
        // 确保prefix有默认值
        if (!isset($config['prefix'])) {
            $config['prefix'] = C('DB_PREFIX', null, '');
        }
        
        // 确保debug有默认值
        if (!isset($config['debug'])) {
            $config['debug'] = C('DB_DEBUG', null, false);
        }
        
        // 确保strict有默认值
        if (!isset($config['strict'])) {
            $config['strict'] = C('DB_STRICT', null, false);
        }
        
        return $config;
    }
    
    /**
     * 根据数据库驱动获取默认端口
     * @param string $driver 数据库驱动
     * @return int
     */
    protected function getDefaultPort($driver)
    {
        $defaultPorts = [
            'mysql'     => 3306,
            'mariadb'   => 3306,
            'pgsql'     => 5432,
            'sqlsrv'    => 1433,
            'sqlite'    => null,
        ];
        
        return isset($defaultPorts[$driver]) ? $defaultPorts[$driver] : 3306;
    }
    
    /**
     * 根据数据库驱动获取默认字符集
     * @param string $driver 数据库驱动
     * @return string
     */
    protected function getDefaultCharset($driver)
    {
        $defaultCharsets = [
            'mysql'     => 'utf8mb4',
            'mariadb'   => 'utf8mb4',
            'pgsql'     => 'utf8',
            'sqlsrv'    => 'utf8',
            'sqlite'    => 'utf8',
        ];
        
        return isset($defaultCharsets[$driver]) ? $defaultCharsets[$driver] : 'utf8';
    }
    
    /**
     * 获取数据库驱动特定的配置
     * @param string $driver 数据库驱动
     * @param array $config 基础配置
     * @return array
     */
    protected function getDriverSpecificConfig($driver, $config)
    {
        $specificConfig = [];
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $specificConfig = [
                    'unix_socket'    => env('DB_SOCKET', ''),
                    'collation'      => env('DB_COLLATION', $config['charset'] . '_general_ci'),
                    'prefix_indexes' => true,
                    'options'        => extension_loaded('pdo_mysql') ? array_filter([
                        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                    ]) : [],
                ];
                break;
                
            case 'pgsql':
                $specificConfig = [
                    'schema'         => 'public',
                    'sslmode'        => 'prefer',
                    'prefix_indexes' => true,
                ];
                break;
                
            case 'sqlsrv':
                $specificConfig = [
                    'prefix_indexes' => true,
                    // 'encrypt' => env('DB_ENCRYPT', 'yes'),
                    // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
                ];
                break;
                
            case 'sqlite':
                $specificConfig = [
                    'database'       => env('DB_DATABASE', database_path('database.sqlite')),
                    'prefix'         => '',
                    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
                ];
                break;
        }
        
        return $specificConfig;
    }
    
    /**
     * 获取查询构建器
     * @param string $table 表名
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table)
    {
        return $this->getConnection()->table($table);
    }
    
    /**
     * Eloquent调试 记录当前SQL
     * @access protected
     * @param boolean $start 调试开始标记 true 开始 false 结束
     * @param string $sql SQL语句
     */
    protected function debug($start, $sql = '')
    {
        if ($this->config['debug'] ?? false) { // 开启数据库调试模式
            if ($start) {
                G('queryStartTime');
                $this->queryStr = $sql;
            } else {
                $this->modelSql['_think_'] = $this->queryStr;
                // 记录操作结束时间
                G('queryEndTime');
                trace($this->queryStr . ' [ RunTime:' . G('queryStartTime', 'queryEndTime') . 's ]', '', 'SQL');
            }
        }
    }
    
    /**
     * 获取SQL语句
     * @param \Illuminate\Database\Query\Builder $query
     * @return string
     */
    protected function getSql($query)
    {
        $bindings = $query->getBindings();
        $sql = str_replace('?', '%s', $query->toSql());
        
        $bindings = array_map(function ($binding) {
            if (is_string($binding)) {
                return "'" . addslashes($binding) . "'";
            } elseif ($binding === null) {
                return 'NULL';
            } else {
                return $binding;
            }
        }, $bindings);
        
        return vsprintf($sql, $bindings);
    }
    
    /**
     * 解析limit参数
     * @param string $limit
     * @return array
     */
    protected function parseLimit($limit)
    {
        if (strpos($limit, ',')) {
            list($offset, $limit) = explode(',', $limit);
            return [intval($offset), intval($limit)];
        } else {
            return [0, intval($limit)];
        }
    }
    
    /**
     * 获取最后插入ID
     * @return mixed
     */
    public function getLastInsID()
    {
        return $this->lastInsID;
    }
    
    /**
     * 获取影响行数
     * @return int
     */
    public function getNumRows()
    {
        return $this->numRows;
    }
    
    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * 获取查询次数
     * @return int
     */
    public function getQueryTimes()
    {
        return $this->queryTimes;
    }
    
    /**
     * 获取执行次数
     * @return int
     */
    public function getExecuteTimes()
    {
        return $this->executeTimes;
    }
    
    /**
     * 获取最后执行的SQL语句
     * @return string
     */
    public function getLastSql()
    {
        return $this->queryStr;
    }
    
    /**
     * 执行查询
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $options 查询选项
     * @return mixed
     */
    public function select($query, $options = [])
    {
        // 应用查询选项
        $query = $this->applyOptions($query, $options);
        
        // 获取SQL语句
        $sql = $this->getSql($query);
        
        // 调试开始
        $this->debug(true, $sql);
        
        try {
            // 执行查询
            $this->queryTimes++;
            N('db_query', 1); // 兼容代码
            
            $result = $query->get();
            $this->numRows = count($result);
            
            // 调试结束
            $this->debug(false);
            
            // 确保返回的是真正的数组，而不是stdClass对象数组
            $arrayResult = $result->map(function ($item) {
                if (is_object($item) && method_exists($item, 'toArray')) {
                    return $item->toArray();
                } elseif ($item instanceof \stdClass) {
                    return (array)$item;
                }
                return $item;
            })->toArray();
            
            return $arrayResult;
        } catch (\Exception $e) {
            // 调试结束
            $this->debug(false);
            
            // 记录错误信息
            $this->error = $e->getMessage();
            if ('' != $sql) {
                $this->error .= "\n [ SQL语句 ] : " . $sql;
            }
            
            // 记录错误日志
            trace($this->error, '', 'ERR');
            
            if ($this->config['debug'] ?? false) {
                E($this->error);
            } else {
                return false;
            }
        }
    }
    
    /**
     * 执行插入
     * @param string $table 表名
     * @param array $data 数据
     * @param array $options 选项
     * @return mixed
     */
    public function insert($table, $data, $options = [])
    {
        $query = $this->table($table);
        $sql = $this->getSql($query);
        
        // 调试开始
        $this->debug(true, $sql);
        
        try {
            // 执行插入
            $this->executeTimes++;
            N('db_write', 1); // 兼容代码
            
            $result = $query->insert($data);
            $this->numRows = $result;
            
            // 获取最后插入ID
            if ($result) {
                $this->lastInsID = $this->getConnection()->getPdo()->lastInsertId();
            }
            
            // 调试结束
            $this->debug(false);
            
            return $result;
        } catch (\Exception $e) {
            // 调试结束
            $this->debug(false);
            
            // 记录错误信息
            $this->error = $e->getMessage();
            if ('' != $sql) {
                $this->error .= "\n [ SQL语句 ] : " . $sql;
            }
            
            // 记录错误日志
            trace($this->error, '', 'ERR');
            
            if ($this->config['debug'] ?? false) {
                E($this->error);
            } else {
                return false;
            }
        }
    }
    
    /**
     * 执行更新
     * @param string $table 表名
     * @param array $data 数据
     * @param array $where 条件
     * @param array $options 选项
     * @return mixed
     */
    public function update($table, $data, $where, $options = [])
    {
        $query = $this->table($table);
        
        // 应用where条件
        if (!empty($where)) {
            $query = $this->applyWhere($query, $where);
        }
        
        // 应用其他选项
        $query = $this->applyOptions($query, $options);
        
        $sql = $this->getSql($query);
        
        // 调试开始
        $this->debug(true, $sql);
        
        try {
            // 执行更新
            $this->executeTimes++;
            N('db_write', 1); // 兼容代码
            
            $result = $query->update($data);
            $this->numRows = $result;
            
            // 调试结束
            $this->debug(false);
            
            return $result;
        } catch (\Exception $e) {
            // 调试结束
            $this->debug(false);
            
            // 记录错误信息
            $this->error = $e->getMessage();
            if ('' != $sql) {
                $this->error .= "\n [ SQL语句 ] : " . $sql;
            }
            
            // 记录错误日志
            trace($this->error, '', 'ERR');
            
            if ($this->config['debug'] ?? false) {
                E($this->error);
            } else {
                return false;
            }
        }
    }
    
    /**
     * 执行删除
     * @param string $table 表名
     * @param array $where 条件
     * @param array $options 选项
     * @return mixed
     */
    public function delete($table, $where, $options = [])
    {
        $query = $this->table($table);
        
        // 应用where条件
        if (!empty($where)) {
            $query = $this->applyWhere($query, $where);
        }
        
        // 应用其他选项
        $query = $this->applyOptions($query, $options);
        
        $sql = $this->getSql($query);
        
        // 调试开始
        $this->debug(true, $sql);
        
        try {
            // 执行删除
            $this->executeTimes++;
            N('db_write', 1); // 兼容代码
            
            $result = $query->delete();
            $this->numRows = $result;
            
            // 调试结束
            $this->debug(false);
            
            return $result;
        } catch (\Exception $e) {
            // 调试结束
            $this->debug(false);
            
            // 记录错误信息
            $this->error = $e->getMessage();
            if ('' != $sql) {
                $this->error .= "\n [ SQL语句 ] : " . $sql;
            }
            
            // 记录错误日志
            trace($this->error, '', 'ERR');
            
            if ($this->config['debug'] ?? false) {
                E($this->error);
            } else {
                return false;
            }
        }
    }
    
    /**
     * 应用查询选项
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $options
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyOptions($query, $options)
    {
        // where条件
        if (!empty($options['where'])) {
            $query = $this->applyWhere($query, $options['where']);
        }
        
        // field字段
        if (!empty($options['field'])) {
            if ($options['field'] instanceof \Think\Db\SQLRaw) {
                $query->selectRaw($options['field']->getValue());
            } else {
                // 将逗号分隔的字段字符串转换为数组
                if (is_string($options['field']) && strpos($options['field'], ',') !== false) {
                    $fields = array_map('trim', explode(',', $options['field']));
                    $query->select($fields);
                } else {
                    $query->select($options['field']);
                }
            }
        }
        
        // order排序
        if (!empty($options['order'])) {
            if (is_array($options['order'])) {
                foreach ($options['order'] as $field => $direction) {
                    $query->orderBy($field, $direction);
                }
            } else {
                $query->orderByRaw($options['order']);
            }
        }
        
        // limit限制
        if (!empty($options['limit'])) {
            list($offset, $limit) = $this->parseLimit($options['limit']);
            $query->offset($offset)->limit($limit);
        }
        
        // group分组
        if (!empty($options['group'])) {
            $query->groupBy($options['group']);
        }
        
        // having条件
        if (!empty($options['having'])) {
            $query->havingRaw($options['having']);
        }
        
        return $query;
    }
    
    /**
     * 应用where条件
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed $where
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyWhere($query, $where)
    {
        if (empty($where)) {
            return $query;
        }
        
        if (is_string($where)) {
            return $query->whereRaw($where);
        }
        
        if (!is_array($where)) {
            return $query;
        }
        
        // 处理逻辑运算符
        $logic = isset($where['_logic']) ? strtoupper($where['_logic']) : 'AND';
        unset($where['_logic']);
        
        foreach ($where as $key => $condition) {
            if (is_numeric($key) && is_array($condition)) {
                // 嵌套条件
                $query->where(function ($q) use ($condition) {
                    $this->applyWhere($q, $condition);
                }, null, null, $logic);
            } else {
                $this->applyWhereCondition($query, $key, $condition, $logic);
            }
        }
        
        return $query;
    }
    
    /**
     * 应用单个where条件
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $key
     * @param mixed $condition
     * @param string $logic
     */
    protected function applyWhereCondition($query, $key, $condition, $logic = 'AND')
    {
        if (strpos($key, '|')) {
            // OR条件
            $fields = explode('|', $key);
            $query->where(function ($q) use ($fields, $condition) {
                foreach ($fields as $field) {
                    $q->orWhere(function ($subQuery) use ($field, $condition) {
                        $this->applyWhereCondition($subQuery, $field, $condition, 'OR');
                    });
                }
            }, null, null, $logic);
        } elseif (strpos($key, '&')) {
            // AND条件
            $fields = explode('&', $key);
            $query->where(function ($q) use ($fields, $condition) {
                foreach ($fields as $field) {
                    $q->where(function ($subQuery) use ($field, $condition) {
                        $this->applyWhereCondition($subQuery, $field, $condition, 'AND');
                    });
                }
            }, null, null, $logic);
        } elseif ($key === '_string') {
            // 原始字符串条件
            $query->whereRaw($condition, [], $logic);
        } elseif ($key === '_complex') {
            // 复杂条件
            $query->where(function ($q) use ($condition) {
                $this->applyWhere($q, $condition);
            }, null, null, $logic);
        } elseif ($key === '_query') {
            // 查询字符串条件
            parse_str($condition, $params);
            foreach ($params as $field => $value) {
                if ($field !== '_logic') {
                    $query->where($field, '=', $value, $logic);
                }
            }
        } else {
            // 普通条件
            $this->applySimpleWhereCondition($query, $key, $condition, $logic);
        }
    }
    
    /**
     * 应用简单where条件
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $key
     * @param mixed $condition
     * @param string $logic
     */
    protected function applySimpleWhereCondition($query, $key, $condition, $logic = 'AND')
    {
        if (is_array($condition)) {
            if (isset($condition[0]) && is_string($condition[0])) {
                $operator = strtolower(trim($condition[0]));
                $value = $condition[1] ?? null;
                
                switch ($operator) {
                    case 'eq':
                        $query->where($key, '=', $value, $logic);
                        break;
                    case 'neq':
                        $query->where($key, '!=', $value, $logic);
                        break;
                    case 'gt':
                        $query->where($key, '>', $value, $logic);
                        break;
                    case 'egt':
                        $query->where($key, '>=', $value, $logic);
                        break;
                    case 'lt':
                        $query->where($key, '<', $value, $logic);
                        break;
                    case 'elt':
                        $query->where($key, '<=', $value, $logic);
                        break;
                    case 'like':
                        $query->where($key, 'like', $value, $logic);
                        break;
                    case 'notlike':
                        $query->where($key, 'not like', $value, $logic);
                        break;
                    case 'in':
                        $query->whereIn($key, (array)$value, $logic);
                        break;
                    case 'notin':
                        $query->whereNotIn($key, (array)$value, $logic);
                        break;
                    case 'between':
                        $query->whereBetween($key, (array)$value, $logic);
                        break;
                    case 'notbetween':
                        $query->whereNotBetween($key, (array)$value, $logic);
                        break;
                    case 'exp':
                        $query->whereRaw($key . ' ' . $value, [], $logic);
                        break;
                    default:
                        $query->where($key, $operator, $value, $logic);
                }
            } else {
                // 多条件数组
                $query->where(function ($q) use ($key, $condition) {
                    foreach ($condition as $item) {
                        if (is_array($item) && isset($item[0])) {
                            $this->applySimpleWhereCondition($q, $key, $item, 'AND');
                        }
                    }
                }, null, null, $logic);
            }
        } else {
            // 简单等值条件
            $query->where($key, '=', $condition, $logic);
        }
    }
    
    /**
     * 获取数据表的字段信息
     * @access public
     * @param string $tableName 表名
     * @return array|false
     */
    public function getFields($tableName)
    {
        // 根据数据库类型获取驱动实例
        $driverType = $this->config['type'] ?? 'mysql';
        try {
            $driver = \Think\DbDriver\DriverFactory::create($driverType);
        } catch (\InvalidArgumentException $e) {
            // 驱动类型不支持，使用默认 MySQL 驱动
            $driver = new \Think\DbDriver\MysqlDriver();
        }
        
        // 构建 SQL
        $sql = $driver->buildGetFieldsSql($tableName, $this->config);
        
        // 调试开始
        $this->debug(true, $sql);
        
        try {
            // 执行查询
            $this->queryTimes++;
            N('db_query', 1); // 兼容代码
            
            $result = $this->getConnection()->select($sql);
            $this->numRows = count($result);
            
            // 调试结束
            $this->debug(false);
            
            if (empty($result)) {
                return false;
            }
            
            // 使用驱动解析结果
            $info = $driver->parseFieldsResult($result, $this->config);
            
            return $info;
            
        } catch (\Exception $e) {
            // 调试结束
            $this->debug(false);
            
            // 记录错误信息
            $this->error = $e->getMessage();
            if ('' != $sql) {
                $this->error .= "\n [ SQL语句 ] : " . $sql;
            }
            
            // 记录错误日志
            trace($this->error, '', 'ERR');
            
            if ($this->config['debug'] ?? false) {
                E($this->error);
            } else {
                return false;
            }
        }
    }

    /**
     * 关闭所有数据库连接
     * @access public
     * @return void
     */
    public function closeAll()
    {
        $capsule = self::getCapsule();
        if (!$capsule) {
            return;
        }

        $manager = $capsule->getDatabaseManager();
        if (!$manager) {
            return;
        }

        $connections = $manager->getConnections();
        foreach ($connections as $connection) {
            try {
                $connection->disconnect();
            } catch (\Exception $e) {
                // 忽略断开连接时的异常
            }
        }
    }
}
