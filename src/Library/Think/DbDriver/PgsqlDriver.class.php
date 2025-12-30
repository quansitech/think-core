<?php

namespace Think\DbDriver;

class PgsqlDriver extends BaseDriver
{
    public function buildGetFieldsSql(string $tableName, array $config): string
    {
        list($tableName, $schema, $hasSchema) = $this->parseTableName($tableName, $config);
        
        if (!$hasSchema) {
            $schema = $this->getDefaultSchema($config);
        }
        
        // 防止 SQL 注入，确保表名和模式名是合法的标识符
        // 这里假设表名和模式名已经经过验证，实际使用中可能需要转义
        $schema = addslashes($schema);
        $tableName = addslashes($tableName);
        
        return "SELECT column_name as field, data_type as type, is_nullable as null, column_default as default, 
                CASE WHEN position('nextval' in column_default) > 0 THEN true ELSE false END as autoinc,
                CASE WHEN constraint_type = 'PRIMARY KEY' THEN true ELSE false END as primary
                FROM information_schema.columns 
                LEFT JOIN information_schema.key_column_usage USING (table_schema, table_name, column_name)
                LEFT JOIN information_schema.table_constraints USING (table_schema, table_name, constraint_name)
                WHERE table_schema = '$schema' AND table_name = '$tableName' 
                ORDER BY ordinal_position";
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
