<?php
namespace Testing;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DBTrait
{
    /**
     * 安装数据库（运行迁移）
     */
    public function install()
    {
        $this->artisan('migrate:refresh --no-cmd');
    }

    /**
     * 卸载数据库（删除所有表、视图、存储过程和事件）
     *
     * @param string $databaseName 数据库名称，默认使用配置中的数据库
     * @return void
     */
    protected function uninstall($databaseName = '')
    {
        if (!$databaseName) {
            $databaseName = $this->getDefaultDatabaseName();
        }

        $connection = DB::connection();
        $driver = $connection->getDriverName();

        // 根据数据库驱动选择合适的清理方式
        switch ($driver) {
            case 'mysql':
                $this->cleanMySQL($connection, $databaseName);
                break;
            case 'pgsql':
                $this->cleanPostgreSQL($connection, $databaseName);
                break;
            case 'sqlite':
                $this->cleanSQLite($connection);
                break;
            case 'sqlsrv':
                $this->cleanSQLServer($connection, $databaseName);
                break;
            default:
                // 使用通用清理方式（只清理表）
                $this->cleanGeneric($connection);
        }
    }

    /**
     * 获取默认数据库名称
     */
    protected function getDefaultDatabaseName(): string
    {
        return config('database.connections.' . config('database.default') . '.database');
    }

    /**
     * 清理 MySQL 数据库
     */
    protected function cleanMySQL(Connection $connection, string $databaseName): void
    {
        // 禁用外键约束检查
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        // 删除所有表
        $tables = $connection->select("
            SELECT table_name
            FROM information_schema.TABLES
            WHERE table_schema = ? AND table_type = 'BASE TABLE'
        ", [$databaseName]);

        foreach ($tables as $table) {
            Schema::dropIfExists($table->table_name);
        }

        // 删除所有视图
        $views = $connection->select("
            SELECT table_name
            FROM information_schema.VIEWS
            WHERE table_schema = ?
        ", [$databaseName]);

        foreach ($views as $view) {
            $connection->statement("DROP VIEW IF EXISTS `{$databaseName}`.`{$view->table_name}`");
        }

        // 删除所有存储过程
        $procedures = $connection->select("
            SELECT ROUTINE_NAME
            FROM information_schema.ROUTINES
            WHERE ROUTINE_SCHEMA = ? AND ROUTINE_TYPE = 'PROCEDURE'
        ", [$databaseName]);

        foreach ($procedures as $procedure) {
            $connection->statement("DROP PROCEDURE IF EXISTS `{$databaseName}`.`{$procedure->ROUTINE_NAME}`");
        }

        // 删除所有事件
        $events = $connection->select("
            SELECT EVENT_NAME
            FROM information_schema.EVENTS
            WHERE EVENT_SCHEMA = ?
        ", [$databaseName]);

        foreach ($events as $event) {
            $connection->statement("DROP EVENT IF EXISTS `{$databaseName}`.`{$event->EVENT_NAME}`");
        }

        // 重新启用外键约束检查
        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * 清理 PostgreSQL 数据库
     */
    protected function cleanPostgreSQL(Connection $connection, string $databaseName): void
    {
        // PostgreSQL 需要先断开所有连接才能删除数据库
        // 这里只删除表和视图等对象

        // 获取所有 schema 中的表（排除系统表）
        $tables = $connection->select("
            SELECT tablename
            FROM pg_tables
            WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
        ");

        foreach ($tables as $table) {
            Schema::dropIfExists($table->tablename);
        }

        // 删除所有视图
        $views = $connection->select("
            SELECT viewname
            FROM pg_views
            WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
        ");

        foreach ($views as $view) {
            $connection->statement("DROP VIEW IF EXISTS \"{$view->viewname}\" CASCADE");
        }

        // 删除所有函数（类似存储过程）
        $functions = $connection->select("
            SELECT routine_name
            FROM information_schema.ROUTINES
            WHERE routine_schema NOT IN ('pg_catalog', 'information_schema')
        ");

        foreach ($functions as $function) {
            $connection->statement("DROP FUNCTION IF EXISTS \"{$function->routine_name}\" CASCADE");
        }
    }

    /**
     * 清理 SQLite 数据库
     */
    protected function cleanSQLite(Connection $connection): void
    {
        // SQLite 简单直接删除文件即可，但这里使用 SQL 方式
        $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            Schema::dropIfExists($table->name);
        }

        // SQLite 不支持存储过程和事件，无需处理
    }

    /**
     * 清理 SQL Server 数据库
     */
    protected function cleanSQLServer(Connection $connection, string $databaseName): void
    {
        // 禁用所有外键约束
        $connection->select("EXEC sp_msforeachtable 'ALTER TABLE ? NOCHECK CONSTRAINT ALL'");

        // 删除所有表
        $tables = $connection->select("
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = 'dbo'
        ");

        foreach ($tables as $table) {
            Schema::dropIfExists($table->TABLE_NAME);
        }

        // 删除所有视图
        $views = $connection->select("
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.VIEWS
            WHERE TABLE_SCHEMA = 'dbo'
        ");

        foreach ($views as $view) {
            $connection->statement("DROP VIEW IF EXISTS [dbo].[{$view->TABLE_NAME}]");
        }

        // 删除所有存储过程
        $procedures = $connection->select("
            SELECT ROUTINE_NAME
            FROM INFORMATION_SCHEMA.ROUTINES
            WHERE ROUTINE_TYPE = 'PROCEDURE' AND ROUTINE_SCHEMA = 'dbo'
        ");

        foreach ($procedures as $procedure) {
            $connection->statement("DROP PROCEDURE IF EXISTS [dbo].[{$procedure->ROUTINE_NAME}]");
        }
    }

    /**
     * 通用清理方式（只清理表和视图）
     * 用于未知或不支持的数据库类型
     */
    protected function cleanGeneric(Connection $connection): void
    {
        // 尝试使用 Schema 获取所有表
        $schemaBuilder = Schema::getConnection()->getSchemaBuilder();

        // 通过 PDO 获取表列表（数据库无关）
        try {
            $tables = $schemaBuilder->getTableListing();

            foreach ($tables as $table) {
                Schema::dropIfExists($table);
            }
        } catch (\Exception $e) {
            // 如果无法获取表列表，尝试使用 SQL
            // 这里是降级处理，不保证所有数据库都支持
        }
    }
}
