<?php
namespace Qscmf\Core;

use App\Models\Node;
use Org\Util\Rbac;
use Illuminate\Database\Capsule\Manager as Capsule;
use Qscmf\Lib\DBCont;

class QsRbac extends Rbac{

    //重写权限认证的过滤器方法
    static public function AccessDecision($appName=MODULE_NAME, $controllerName=CONTROLLER_NAME, $actionName=ACTION_NAME) {

        \Think\Hook::listen('before_auth');

        //检查是否需要认证
        if(self::gyCheckAccess($appName, $controllerName, $actionName)) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid   =   md5(strtoupper($appName).strtoupper($controllerName).strtoupper($actionName));
            if(!session(C('ADMIN_AUTH_KEY'))) {
                if(C('USER_AUTH_TYPE')==2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $userId = session(C('USER_AUTH_KEY'));
                    $accessList = self::getAccessListForQsRbac($userId);
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                if(!isset($accessList[strtoupper($appName)][strtoupper($controllerName)][strtoupper($actionName)])) {
                    $_SESSION[$accessGuid]  =   false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid]	=	true;
                }
            }else{
                //管理员无需认证
				return true;
			}
        }
        return true;
    }
    
    static function gyCheckAccess($appName=MODULE_NAME, $controllerName=CONTROLLER_NAME, $actionName=ACTION_NAME) {
        $node_model = new Node();
        $map['name'] = ucfirst($appName);
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['level'] = DBCont::LEVEL_MODULE;
        $node = $node_model->getNode($map);
        
        //不存在该权限点，则无需权限控制
        if(is_null($node)){
            return false;
        }
        
        $map['name'] = ucfirst($controllerName);
        $map['pid'] = $node['id'];
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['level'] = DBCont::LEVEL_CONTROLLER;
        $node = $node_model->getNode($map);
         if(is_null($node)){
            return false;
        }

        $map['name'] = ucfirst($actionName);
        $map['pid'] = $node['id'];
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['level'] = DBCont::LEVEL_ACTION;
        $node = $node_model->getNode($map);
         if(is_null($node)){
            return false;
        }
        
        return true;
    }
    
    //检查用户有无node_id的接入权限
    //return  true代表有   false代表无
    static function checkAccessNodeId($auth_id, $node_id){
        //超级管理员拥有所有权限
        if(session(C('ADMIN_AUTH_KEY'))){
            return true;
        }

        $roleTable = C('RBAC_ROLE_TABLE');
        $userTable = C('RBAC_USER_TABLE');
        $accessTable = C('RBAC_ACCESS_TABLE');
        $nodeTable = C('RBAC_NODE_TABLE');

        $count = Capsule::table("{$userTable} as user")
            ->join("{$roleTable} as role", 'user.role_id', '=', 'role.id')
            ->join("{$accessTable} as access", function($join) {
                $join->on('access.role_id', '=', 'role.id')
                     ->orOn(function($query) {
                         $query->on('access.role_id', '=', 'role.pid')
                               ->where('role.pid', '!=', 0);
                     });
            })
            ->join("{$nodeTable} as node", 'access.node_id', '=', 'node.id')
            ->where('user.user_id', $auth_id)
            ->where('role.status', 1)
            ->where('node.status', 1)
            ->where('node.id', $node_id)
            ->count();

        return $count > 0;
    }
    
    
    static function checkAccess() {

    }

    /**
     * 使用 Capsule 查询构造器（非原生SQL）获取用户权限列表
     */
    static function getAccessListForQsRbac($authId) {
        $roleTable = C('RBAC_ROLE_TABLE');
        $userTable = C('RBAC_USER_TABLE');
        $accessTable = C('RBAC_ACCESS_TABLE');
        $nodeTable = C('RBAC_NODE_TABLE');

        // 构建基础查询（复用四表关联）
        $getNodes = function ($level, $pid = null) use ($authId, $roleTable, $userTable, $accessTable, $nodeTable) {
            return Capsule::table("{$userTable} as user")
                ->join("{$roleTable} as role", 'user.role_id', '=', 'role.id')
                ->join("{$accessTable} as access", function ($join) use ($accessTable, $roleTable) {
                    $join->on('access.role_id', '=', 'role.id');
                    $join->orOn(function ($q) {
                        $q->on('access.role_id', '=', 'role.pid')
                          ->where('role.pid', '!=', 0);
                    });
                })
                ->join("{$nodeTable} as node", 'access.node_id', '=', 'node.id')
                ->where('user.user_id', $authId)
                ->where('role.status', 1)
                ->where('node.status', 1)
                ->where('node.level', $level)
                ->when($pid !== null, function ($q) use ($pid) {
                    $q->where('node.pid', $pid);
                })
                ->select('node.id', 'node.name')
                ->get();
        };

        // 获取 level=1 的应用节点
        $apps = $getNodes(1);

        $access = [];
        foreach ($apps as $app) {
            $appId = $app->id;
            $appName = $app->name;
            $access[strtoupper($appName)] = [];

            // 获取 level=2 的模块
            $modules = $getNodes(2, $appId);

            $publicAction = [];
            $modulesList = [];
            $publicKey = null;

            // 找到 PUBLIC 模块
            foreach ($modules as $module) {
                $modulesList[] = $module;
                if (strtoupper($module->name) === 'PUBLIC') {
                    $publicKey = $module->id;
                }
            }

            // 提取 PUBLIC 模块的 action
            if ($publicKey !== null) {
                $rs = $getNodes(3, $publicKey);
                foreach ($rs as $a) {
                    $publicAction[$a->name] = $a->id;
                }
                // 从列表中移除 PUBLIC
                $modulesList = array_filter($modulesList, function ($m) {
                    return strtoupper($m->name) !== 'PUBLIC';
                });
            }

            // 获取其他模块的 action（level=3）
            foreach ($modulesList as $module) {
                $rs = $getNodes(3, $module->id);
                $action = [];
                foreach ($rs as $a) {
                    $action[$a->name] = $a->id;
                }
                $action += $publicAction;
                $access[strtoupper($appName)][strtoupper($module->name)] = array_change_key_case($action, CASE_UPPER);
            }
        }

        return $access;
    }
}
