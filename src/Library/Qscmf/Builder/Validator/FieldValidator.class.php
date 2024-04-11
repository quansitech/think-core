<?php

namespace Qscmf\Builder\Validator;

class FieldValidator {

    protected array $valid_rules = [
        'required',
        'email',
        'url',
        'dateISO',
        'number',
        'digits',
//        'equalTo', // 有bug
        'maxlength',
        'minlength',
        'rangelength',
        'range',
        'max',
        'min',
    ];

    public function __construct(
        public string $field,
        public array $rules = []
    ) {}

    public function addRule(array|string $rule): void {
        $def_params = true;
        $def_message = null;

        // 适应不同的规则定义格式
        if (is_string($rule)) {
            $rule = [$rule, $def_params, $def_message];
        }

        // 解构数组到变量，索引数组追加不会被替换
        [$rule_name, $parameter, $message] = $rule + [null, $def_params, $def_message];
        $rule_name = $this->_ruleMapping($rule_name);
        $this->validateRule($rule_name);

        $this->rules[] = [
            'rule'    => $rule_name,
            'params'  => $parameter,
            'message' => $message
        ];
    }

    protected function validateRule(string $rule_name):bool{
        $is_valid = in_array($rule_name, $this->valid_rules, true);
        if (!$is_valid){
            E("验证规则无效： ".$rule_name);
        }

        return true;
    }

    private function _ruleMapping(string $rule):string{
        return match($rule){
            'date' => 'dateISO',
            'max_length' => 'maxlength',
            'min_length' => 'minlength',
            'range_length' => 'rangelength',
            default => $rule
        };
    }

    public function getRules(): array {
        return $this->rules;
    }
}