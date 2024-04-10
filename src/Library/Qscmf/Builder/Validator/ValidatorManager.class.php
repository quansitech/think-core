<?php

namespace Qscmf\Builder\Validator;

class ValidatorManager {
    protected array $fields = [];
    protected array $sub_table_field = [];
    public static string $sub_table_name = 'QSCMF_VALIDATOR_SUB_TABLE_NAME';

    public function addRulesFromArray(array $fields_rules): void {
        foreach ($fields_rules as $field => $rules) {
            if ($this->_isSubTable($rules)){
                $this->_addSubTableRulesFromArray($rules);
            }else{
                $validator = new FieldValidator(field: $field);

                foreach ($rules as $rule) {
                    $validator->addRule($rule);
                }

                $this->_addFieldValidator($validator);
            }
        }
    }

    private function _addSubTableRulesFromArray(array $rules):void{
        unset($rules['type']);
        $this->sub_table_field = array_merge($this->sub_table_field,array_keys($rules['rules']));
        $this->addRulesFromArray($rules['rules']);
    }

    private function _isSubTable(string|array $rules): bool {
        if (!is_array($rules)){
            return false;
        }
        if (!isset($rules['type'])){
            return false;
        }
        if ($rules['type'] !== 'sub_table'){
            return false;
        }

        return true;
    }

    private function _addFieldValidator(FieldValidator $validator): void {
        $this->fields[$validator->field] = $validator->getRules();
    }

    public function getValidationRulesAndMessages(): array {
        $rules = [
            'rules' => [],
            'messages' => [],
            'sub_table_field' => $this->sub_table_field,
            'sub_table_name' => self::$sub_table_name
        ];

        foreach ($this->fields as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $rules['rules'][$field][$rule['rule']] = $rule['params']; // 默认参数为 true
                $rule['message'] && $rules['messages'][$field][$rule['rule']] = $rule['message'];
            }
        }

        return $rules;
    }

    public function toArray(): array {
        return $this->getValidationRulesAndMessages();
    }

    public function toJson(): string {
        return json_encode($this->getValidationRulesAndMessages());
    }

    public static function rebuildKeys(array $form_data, array $sub_table_key): array {
        foreach ($sub_table_key as $key) {
            $form_data[$key] = array_values($form_data[$key]);
        }
        return $form_data;
    }


    public static function reIndexSubTableData(?array $post_data = []):array{
        if (empty($post_data)){
            return  $post_data;
        }

        if (empty($post_data[self::$sub_table_name])){
            return $post_data;
        }
        $sub_table_name_arr = explode(',', $post_data[self::$sub_table_name]);

        unset($post_data[self::$sub_table_name]);

        $form_data_key = array_keys($post_data);
        $has_sub_table_name = array_intersect($sub_table_name_arr, $form_data_key);
        if (empty($has_sub_table_name)){
            return $post_data;
        }

        return self::rebuildKeys($post_data, $has_sub_table_name);
    }

}