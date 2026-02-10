<?php

namespace Qscmf\Core;

use Illuminate\Database\Eloquent\Model;
use \Watson\Validating\ValidatingTrait;

/**
 * 基础模型类
 *
 * 所有 Laravel Eloquent 模型都应该继承此类
 * 提供：
 * 1. watson/validating 验证功能
 * 2. ThinkPHP 混合环境所需的 Validator 实例获取逻辑
 * 3. 删除前验证功能（类似 ThinkPHP 的 $_delete_validate）
 * 4. 联动删除功能（类似 ThinkPHP 的 $_delete_auto）
 *
 * 验证失败统一返回 false，通过 $model->getErrors() 获取错误信息
 */
abstract class BaseModel extends Model
{
    use ValidatingTrait;

    /**
     * 验证规则（子类可以覆盖）
     *
     * @var array
     */
    protected $rules = [];

    /**
     * 删除验证规则（子类可以覆盖）
     *
     * @var array
     */
    protected $deleteRules = [];

    /**
     * 验证错误消息（子类可以覆盖）
     *
     * @var array
     */
    protected $validationMessages = [];

    /**
     * 删除验证错误消息（子类可以覆盖）
     *
     * @var array
     */
    protected $deleteValidationMessages = [];

    /**
     * 删除时自动删除的关联表配置
     *
     * 使用示例：
     * protected $deleteCascade = [
     *     ['table' => 'role_user', 'foreign_key' => 'user_id'],
     *     ['table' => 'user_profile', 'foreign_key' => 'user_id'],
     * ];
     *
     * @var array
     */
    protected $deleteCascade = [];

    /**
     * Boot 方法 - 注册删除事件监听
     */
    protected static function boot()
    {
        parent::boot();

        // 监听删除事件
        static::deleting(function ($model) {
            // 先执行验证
            if ($model->validateBeforeDelete() === false) {
                return false;
            }

            // 验证通过后，执行联动删除
            $model->cascadeDelete();
        });
    }

    /**
     * 获取 Validator 实例（重写以支持 ThinkPHP 混合环境）
     *
     * @return \Illuminate\Validation\Factory
     */
    public function getValidator()
    {
        if ($this->validator) {
            return $this->validator;
        }

        // 从全局变量获取（在 EloquentLoadBehavior 中初始化）
        if (isset($GLOBALS['laravel_validator_factory'])) {
            return $GLOBALS['laravel_validator_factory'];
        }

        // 尝试使用 Facade（如果已初始化）
        try {
            return \Illuminate\Support\Facades\Validator::getFacadeRoot();
        } catch (\Exception $e) {
            throw new \Exception('Validator 未初始化，请检查 EloquentLoadBehavior');
        }
    }

    /**
     * 删除前验证
     *
     * @return bool
     */
    public function validateBeforeDelete()
    {
        // 如果没有删除规则，直接返回 true
        $rules = $this->getDeleteRules();
        if (empty($rules)) {
            return true;
        }

        // 获取验证器实例
        $validator = $this->getValidator();

        // 注册自定义验证规则：no_related
        // 用于检查关联表中是否存在记录
        // 用法：no_related:表名,外键字段
        $validator->extend('no_related', function ($attribute, $value, $parameters) {
            // $parameters[0] = 数据库表名（如 syslogs）
            // $parameters[1] = 外键字段名（如 userid）
            $tableName = $parameters[0];
            $foreignKey = $parameters[1];

            // 使用 Capsule 查询（避免与 ThinkPHP 的 DB 冲突）
            $count = \Illuminate\Database\Capsule\Manager::table($tableName)
                ->where($foreignKey, $this->id)
                ->count();

            return $count === 0;
        });

        // 执行验证
        $validation = $validator->make(
            $this->toArray(),
            $rules,
            $this->getDeleteValidationMessages()
        );

        if ($validation->fails()) {
            // 保存错误信息
            $this->setErrors($validation->messages());

            return false;
        }

        return true;
    }

    /**
     * 获取删除验证规则
     *
     * @return array
     */
    public function getDeleteRules()
    {
        return isset($this->deleteRules) ? $this->deleteRules : [];
    }

    /**
     * 获取删除验证错误消息
     *
     * @return array
     */
    public function getDeleteValidationMessages()
    {
        return isset($this->deleteValidationMessages) ? $this->deleteValidationMessages : [];
    }

    /**
     * 联动删除关联数据
     *
     * @return void
     */
    public function cascadeDelete()
    {
        $cascade = $this->getDeleteCascade();
        if (empty($cascade)) {
            return;
        }

        foreach ($cascade as $config) {
            $tableName = $config['table'] ?? null;
            $foreignKey = $config['foreign_key'] ?? null;

            if (!$tableName || !$foreignKey) {
                continue;
            }

            // 删除关联表中符合条件的记录
            \Illuminate\Database\Capsule\Manager::table($tableName)
                ->where($foreignKey, $this->id)
                ->delete();
        }
    }

    /**
     * 获取联动删除配置
     *
     * @return array
     */
    public function getDeleteCascade()
    {
        return isset($this->deleteCascade) ? $this->deleteCascade : [];
    }
}
