<?php

namespace Qscmf\Builder\Validator;

trait TValidator
{
    public ValidatorManager $validator;
    public bool $need_validate = false;

    public function addRulesFromArray(array $fields_rules): void {
        $this->need_validate = true;
        $this->validator = (new ValidatorManager());

        $this->validator->addRulesFromArray($fields_rules);
    }

    public function needValidate():bool{
        return $this->need_validate;
    }

    public function getValidateList():array{
        if ($this->needValidate()){
            return $this->validator->toArray();
        }
        return [];
    }

}