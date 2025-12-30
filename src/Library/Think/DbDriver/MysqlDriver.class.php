<?php

namespace Think\DbDriver;

class MysqlDriver extends BaseDriver
{
    public function buildGetFieldsSql(string $tableName, array $config): string
    {
        list($tableName, $database, $hasDatabase) = $this->parseTableName($tableName, $config);
        
        // 防止 SQL 注入，使用反引号包裹标识符
        $tableName = '`' . addslashes($tableName) . '`';
        
        if ($hasDatabase && $database) {
            $database = '`' . addslashes($database) . '`';
            return "SHOW COLUMNS FROM $database.$tableName";
        } else {
            return "SHOW COLUMNS FROM $tableName";
        }
    }

    public function parseFieldsResult(array $result, array $config): array
    {
        $info = [];
        foreach ($result as $val) {
            // 统一字段名大小写
            $val = array_change_key_case((array)$val, CASE_LOWER);
            
            $field = $val['field'] ?? '';
            if (!$field) {
                continue;
            }
            
            $info[$field] = [
                'name'    => $field,
                'type'    => $val['type'] ?? '',
                'notnull' => ($val['null'] ?? '') === 'NO',
                'default' => $val['default'] ?? null,
                'primary' => strtolower($val['key'] ?? '') == 'pri',
                'autoinc' => strpos(strtolower($val['extra'] ?? ''), 'auto_increment') !== false,
            ];
        }
        
        return $info;
    }
}
