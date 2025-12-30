<?php

namespace Think\DbDriver;

class SqliteDriver extends BaseDriver
{
    public function buildGetFieldsSql(string $tableName, array $config): string
    {
        list($tableName, $_, $_) = $this->parseTableName($tableName, $config);
        
        // SQLite 使用 PRAGMA table_info，表名用单引号包裹
        $tableName = addslashes($tableName);
        return "PRAGMA table_info('$tableName')";
    }

    public function parseFieldsResult(array $result, array $config): array
    {
        $info = [];
        foreach ($result as $val) {
            // 统一字段名大小写
            $val = array_change_key_case((array)$val, CASE_LOWER);
            
            $field = $val['name'] ?? ''; // SQLite 返回的字段名是 'name'
            if (!$field) {
                continue;
            }
            
            $info[$field] = [
                'name'    => $field,
                'type'    => $val['type'] ?? '',
                'notnull' => ($val['notnull'] ?? 0) == 1,
                'default' => $val['dflt_value'] ?? null,
                'primary' => ($val['pk'] ?? 0) == 1,
                'autoinc' => false, // SQLite 需要额外检查，这里先设为 false
            ];
        }
        
        return $info;
    }
}
