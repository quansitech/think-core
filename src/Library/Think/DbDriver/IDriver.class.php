<?php

namespace Think\DbDriver;

interface IDriver
{
    /**
     * 构建获取表字段信息的SQL语句
     *
     * @param string $tableName 表名（可能包含数据库名或模式名）
     * @param array $config 数据库配置（包含type等）
     * @return string SQL语句
     */
    public function buildGetFieldsSql(string $tableName, array $config): string;

    /**
     * 解析数据库返回的字段信息结果
     *
     * @param array $result 数据库查询结果（每行作为一个数组）
     * @param array $config 数据库配置
     * @return array 统一格式的字段信息数组
     */
    public function parseFieldsResult(array $result, array $config): array;
}
