<?php

namespace Think\DbDriver;

class DriverFactory
{
    /**
     * 根据数据库类型创建驱动实例
     *
     * @param string $driverType 数据库类型（mysql, pgsql, sqlite, sqlsrv 等）
     * @return IDriver
     * @throws \InvalidArgumentException 当驱动类型不支持时
     */
    public static function create(string $driverType): IDriver
    {
        $driverType = strtolower($driverType);
        
        switch ($driverType) {
            case 'mysql':
            case 'mysqli':
            case 'mariadb':
                return new MysqlDriver();
            case 'pgsql':
                return new PgsqlDriver();
            case 'sqlite':
                return new SqliteDriver();
            case 'sqlsrv':
                return new SqlsrvDriver();
            default:
                // 默认使用 MySQL 驱动
                return new MysqlDriver();
        }
    }
}
