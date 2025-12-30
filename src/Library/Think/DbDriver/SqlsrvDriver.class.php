<?php

namespace Think\DbDriver;

class SqlsrvDriver extends BaseDriver
{
    public function buildGetFieldsSql(string $tableName, array $config): string
    {
        list($tableName, $schema, $hasSchema) = $this->parseTableName($tableName, $config);
        
        if (!$hasSchema) {
            $schema = $this->getDefaultSchema($config);
        }
        
        // 防止 SQL 注入，确保表名和模式名是合法的标识符
        $schema = addslashes($schema);
        $tableName = addslashes($tableName);
        
        return "SELECT COLUMN_NAME as field, DATA_TYPE as type, 
                CASE WHEN IS_NULLABLE = 'YES' THEN 'YES' ELSE '' END as null, 
                COLUMN_DEFAULT as default,
                CASE WHEN COLUMNPROPERTY(object_id('$schema.$tableName'), COLUMN_NAME, 'IsIdentity') = 1 THEN 1 ELSE 0 END as autoinc,
                CASE WHEN EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k 
                    WHERE k.TABLE_NAME = c.TABLE_NAME AND k.COLUMN_NAME = c.COLUMN_NAME
                    AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc 
                              WHERE tc.TABLE_NAME = k.TABLE_NAME AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY')
                ) THEN 1 ELSE 0 END as primary
                FROM INFORMATION_SCHEMA.COLUMNS c 
                WHERE TABLE_NAME = '$tableName' AND TABLE_SCHEMA = '$schema'";
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
                'primary' => !empty($val['primary']),
                'autoinc' => !empty($val['autoinc']),
            ];
        }
        
        return $info;
    }
}
