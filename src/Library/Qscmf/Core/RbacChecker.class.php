<?php
namespace Qscmf\Core;

use Qscmf\Contracts\RbacCheckerInterface;

/**
 * RBAC 权限决策器默认实现。
 *
 * 包装现有的 QsRbac 静态方法调用。改造后 QsController 不再直接调
 * QsRbac::AccessDecision()，而是通过注入的 RbacCheckerInterface 调用本类，
 * 这样测试时可替换容器绑定实现 mock，无需修改 QsController 源码。
 *
 * 生产环境行为与改造前完全一致：所有调用最终都委托给 QsRbac 静态方法。
 */
class RbacChecker implements RbacCheckerInterface
{
    public function accessDecision(): bool
    {
        return QsRbac::AccessDecision();
    }

    public function checkAccessNodeId($authId, int $nodeId): bool
    {
        return QsRbac::checkAccessNodeId($authId, $nodeId);
    }
}
