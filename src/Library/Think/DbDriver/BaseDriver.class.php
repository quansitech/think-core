<?php

namespace Think\DbDriver;

abstract class BaseDriver implements IDriver
{
    /**
     * 解析表名，移除别名，分离数据库/模式部分
     *
     * @param string $tableName 原始表名
     * @param array $config 数据库配置
     * @return array [表名, 数据库名/模式名, 是否包含数据库/模式]
     */
    protected function parseTableName(string $tableName, array $config): array
    {
        // 移除可能的别名（如 "table AS alias"）
        list($tableName) = explode(' ', $tableName);

        $database = null;
        $schema = null;
        $hasSchema = false;

        // 根据数据库类型判断分隔符
        $driver = strtolower($config['type'] ?? 'mysql');
        if (strpos($tableName, '.')) {
            if (in_array($driver, ['pgsql', 'sqlsrv'])) {
                // PostgreSQL 和 SQL Server 使用 schema.table
                list($schema, $tableName) = explode('.', $tableName);
                $hasSchema = true;
            } else {
                // MySQL 等使用 database.table
                list($database, $tableName) = explode('.', $tableName);
                $hasSchema = false;
            }
        }

        return [$tableName, $database ?? $schema, $hasSchema];
    }

    /**
     * 获取默认的模式名或数据库名
     *
     * @param array $config 数据库配置
     * @return string
     */
    protected function getDefaultSchema(array $config): string
    {
        $driver = strtolower($config['type'] ?? 'mysql');
        switch ($driver) {
            case 'pgsql':
                return 'public';
            case 'sqlsrv':
                return 'dbo';
            default:
                return '';
        }
    }

    abstract public function buildGetFieldsSql(string $tableName, array $config): string;
    abstract public function parseFieldsResult(array $result, array $config): array;
}
