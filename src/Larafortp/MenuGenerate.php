<?php

namespace Larafortp;

use Illuminate\Support\Facades\DB;

/*
 * 生成菜单和节点列表
 * 自动处理menu和node的关系
 *
 */
class MenuGenerate
{
    public $menu_id = 0; //菜单的节点id      node::level=3时node::pid使用这个值，
    public $node_pid = 0; //父节点的id，即控制器的id的 node::level=3时pid使用这个值
    public $menu_pid = 0; //菜单的pid     menu::level=2时menu pid使用这个值（这个值产生于插入头部导航栏）
    public $module_id = 0; //模块id        node::level=2时pid使用这个值

    /**
     * 多条记录插入->>二维数组.
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function insertAll($data)
    {
        DB::beginTransaction();

        try {
            foreach ($data as $key => $datum) {
                $this->insert($key, $datum);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
        DB::commit();
    }

    /**
     * 多条记录插入->>三维数组.
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function insertNavigationAll($data)
    {
        DB::beginTransaction();

        try {

            //取出每一个top_menu
            foreach ($data as $item) {
                $this->insertNavigation($item);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
        DB::commit();
    }

    public function insertNavigation($data)
    {
        //这个方法处理数据的
        $data['type'] = 'top_menu';
        //等级为1
        $data['level'] = 1;
        $data['pid'] = 0;
        //传创建top_menu
        $this->insertMenu($data);
        //创建模块
        $this->insertNodeModul($data);
        //创建菜单和节点
        foreach ($data['top_menu'] as $key => $item) {
            $menuData['title'] = $key;
            $menuData['level'] = 2;
            $menuData['pid'] = $this->menu_pid;
            $this->insert($menuData, $item);
        }
    }

    /*
     * $menuData 为字符串或者数组
     * $contronller 二维数组
     * $contronller = array(
        array(
            name=>//名称
            title=>//标题
            sort=>排序
            icon=> icon
            remark=> 备注
            controller=> 控制器
            status=>状态
        )
     );
     *
     */
    public function insert($menuData, $controlAction)
    {
        $controller = [];

        try {
            $this->insertMenu($menuData);
            foreach ($controlAction as $item) {
                if (empty($item)) {
                    throw new \Exception('数据为空');
                }
                $controller['name'] = $item['controller'];
                if (!empty($this->module_id)) {
                    $controller['pid'] = $this->module_id;
                    $controller['menu_id'] = 0;
                }
                $this->insertNodeContronller($controller);
                $this->insertNodeAction($item);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *插入数据到menu
     * $tableData =array('title'=>);
     * $tablData = 'string';
     * 注意只有title是必填项
     * 这个主要处理menu的逻辑关系.
     */
    public function insertMenu($tableData)
    {
        if (empty($tableData)) {
            throw new \Exception('insertMenu Null');
        }
        /*
         * 菜单数据有两种level=1,为头部导航，level=2为左边导航
         */
        if (is_array($tableData)) {
            //获取数据
            if (isset($tableData['level']) && $tableData['level'] == 1) {
                $firstData = DB::table('qs_menu')->where('title', $tableData['title'])->where('level', 1)->first();
            } else {
                if (!empty($this->menu_pid)) {
                    $firstData = DB::table('qs_menu')->where('title', $tableData['title'])->where('level', 2)->where('pid', $this->menu_pid)->first();
                } else {
                    $firstData = DB::table('qs_menu')->where('title', $tableData['title'])->where('level', 2)->first();
                }
            }
            if (isset($firstData->level) && $firstData->level == 1) {
                $this->menu_pid = $firstData->id;
            } elseif (isset($firstData->level) && $firstData->level == 2) {
                $this->menu_id = $firstData->id;
            } else {
                $data = $this->createMenuDataArray($tableData);
                $this->menu_id = DB::table('qs_menu')->insertGetId($data);
                if ($data['level'] == 1) {
                    $this->menu_pid = $this->menu_id;
                }
            }
        } else {
            $firstData = DB::table('qs_menu')->where('title', $tableData)->where('level', 2)->first();
            if (empty($firstData)) {
                $data = $this->createMenuDataArray($tableData);
                $this->menu_id = DB::table('qs_menu')->insertGetId($data);
            } else {
                $this->menu_id = $firstData->id;
            }
        }
    }

    //处理模块的逻辑关系
    public function insertNodeModul($tableData)
    {
        if (empty($tableData['module']) || empty($tableData['module_name'])) {
            throw new \Exception('模块创建异常,模块名为空');
        }
        $firstData = DB::table('qs_node')->where('name', $tableData['module'])->where('level', 1)->first();
        if (empty($firstData)) {
            $data = [];
            $data['name'] = $tableData['module'];
            $data['title'] = $tableData['module_name'];
            $data['sort'] = 0;
            $data['pid'] = 0;
            $data['level'] = 1;
            $data['menu_id'] = 0;
            $data['icon'] = '';
            $data['remark'] = '';
            $data['status'] = 1;
            $this->module_id = DB::table('qs_node')->insertGetId($data);
        } else {
            $this->module_id = $firstData->id;
        }
    }

    /**
     * 创建node数据中的控制器数据，
     * 数据格式应为
     *$tableData =array('name'=>);
     * $tablData = 'string';
     * 这个处理控制器的逻辑关系.
     */
    public function insertNodeContronller($tableData)
    {
        //查找控制器是否存在
        //添加控制器
        if (!empty($this->module_id)) {
            $tableData['pid'] = $this->module_id;
        } else {
            $tableData['pid'] = 1;
        }
        $data = $this->createNodeController($tableData);
        if (!empty($this->module_id)) {
            $firstData = DB::table('qs_node')->where('name', $data['name'])->where('pid', $this->module_id)->where('level', 2)->first();
        } else {
            $firstData = DB::table('qs_node')->where('name', $data['name'])->where('level', 2)->where('pid', 1)->first();
        }
        if (!empty($firstData)) {
            $this->node_pid = $firstData->id;
        } else {
            $this->node_pid = DB::table('qs_node')->insertGetId($data);
        }
    }

    /**
     * 创建node数据中的方法数据，传值为
     * $data['name'] 和 $data['title'] 必须.
     */
    public function insertNodeAction($tableData)
    {
        $data = $this->createNodeAction($tableData);
        $map = [];
        //如果一下数据在数据库的某条记录中已经存在，则认为重复了
        $map['name'] = $data['name'];
        $map['title'] = $data['title'];
        $map['level'] = $data['level'];
        $map['pid'] = $data['pid'];
        $map['menu_id'] = $data['menu_id'];
        //查重
        $repeat = DB::table('qs_node')->where($map)->first();
        //重复直接返回
        if (!empty($repeat)) {
            return true;
        }
        $id = DB::table('qs_node')->insertGetId($data);
        if (empty($id)) {
            throw new \Exception($data['name'].'方法创建异常');
        } else {
            return $id;
        }
    }

    /**
     *创建menu数据
     * 负责数据生成.
     */
    private function createMenuDataArray($tableData)
    {
        $data = [];
        if (is_array($tableData)) {
            //检验标题
            if (isset($tableData['title']) && !empty($tableData['title'])) {
                $data['title'] = $tableData['title'];
            } else {
                throw new \Exception('菜单title为空请检查');
            }
            $data['sort'] = isset($tableData['sort']) ? (int) $tableData['sort'] : 0;
            $data['icon'] = isset($tableData['icon']) ? $tableData['icon'] : '';
            $data['type'] = (isset($tableData['type']) && !empty($tableData['type'])) ? $tableData['type'] : 'backend_menu';
            $data['url'] = isset($tableData['url']) ? $tableData['url'] : '';
            $data['pid'] = isset($tableData['pid']) ? $tableData['pid'] : $this->menu_pid;
            $data['module'] = isset($tableData['module']) ? $tableData['module'] : '';
            $data['status'] = isset($tableData['status']) ? (int) $tableData['status'] : 1;
            $data['level'] = isset($tableData['level']) ? (int) $tableData['level'] : 2;
        } else {
            if (empty($tableData)) {
                throw new \Exception('错误，菜单数据为空');
            }
            $data = [
                'title'  => $tableData,
                'sort'   => 10,
                'icon'   => 'fa-list',
                'type'   => 'backend_menu',
                'url'    => '',
                'pid'    => 3,
                'module' => '',
                'status' => 1,
                'level'  => 2,
            ];
        }

        return $data;
    }

    /**
     *创建node 控制器的数据
     * 数组$data['name'] 必须
     * 如果是传string，则需要传控制器名，.
     */
    private function createNodeController($data)
    {
        $ControllerData = [];
        //检验标题
        if (isset($data['name']) && !empty($data['name'])) {
            $ControllerData['name'] = $data['name'];
        } else {
            throw new \Exception('控制器名为空请检查');
        }
        $ControllerData['title'] = $ControllerData['name'];
        $ControllerData['status'] = isset($data['status']) ? (int) $data['status'] : 1;
        $ControllerData['remark'] = isset($data['remark']) ? $data['remark'] : '';
        $ControllerData['sort'] = isset($data['sort']) ? (int) $data['sort'] : 0;
        $ControllerData['pid'] = (isset($data['pid']) && !empty($data['pid'])) ? (int) $data['pid'] : (empty($this->node_pid) ? 1 : $this->node_pid);
        $ControllerData['level'] = isset($data['level']) ? (int) $data['level'] : 2;
        $ControllerData['menu_id'] = isset($data['menu_id']) ? (int) $data['menu_id'] : (empty($this->menu_pid) ? 0 : $this->menu_pid);
        $ControllerData['icon'] = isset($data['icon']) ? $data['icon'] : '';
        $ControllerData['url'] = isset($data['url']) ? $data['url'] : '';

        return $ControllerData;
    }

    /**
     *创建node action数据（即方法、函数）
     * 注意必填项为$data['name']、$data['title'].
     */
    private function createNodeAction($data)
    {
        $actionData = [];
        if (is_array($data) && !empty($data)) {
            //检验标题
            if ((isset($data['name']) && !empty($data['name']))) {
                $actionData['name'] = $data['name'];
            } else {
                throw new \Exception('方法名名为空请检查');
            }
            if (isset($data['title']) && !empty($data['title'])) {
                $actionData['title'] = $data['title'];
            } else {
                throw new \Exception('标题为空请检查');
            }
            $actionData['status'] = (isset($data['status']) && !empty($data['status'])) ? (int) $data['status'] : 1;
            $actionData['remark'] = isset($data['remark']) ? $data['remark'] : '';
            $actionData['sort'] = isset($data['sort']) ? (int) $data['sort'] : 0;
            $actionData['pid'] = (isset($data['pid']) && !empty($data['pid'])) ? (int) $data['pid'] : $this->node_pid;
            $actionData['level'] = (isset($data['level']) && !empty($data['level'])) ? (int) $data['level'] : 3;
            $actionData['menu_id'] = (isset($data['menu_id']) && !empty($data['menu_id'])) ? (int) $data['menu_id'] : $this->menu_id;
            $actionData['icon'] = isset($data['icon']) ? $data['icon'] : '';
            $actionData['url'] = isset($data['url']) ? $data['url'] : '';
        } else {
            throw new \Exception('错误，方法的数据格式不正确');
        }

        return $actionData;
    }

    /**
     * 使用insertNavigationAll生成则是这个方法删除.
     *回滚部分.
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function insertNavigationAllRollback($data)
    {
        DB::beginTransaction();

        try {
            //取出每一个top_menu
            foreach ($data as $item) {
                $this->setMenuPID($item['title']);
                $this->setModuleId($item['module']);
                //处理菜单列表
                foreach ($item['top_menu'] as $key => $menu) {
                    $this->handleMenuNode($key, $menu);
                }
                if (!$this->countChildrenMenu($this->menu_pid)) {
                    $this->deleteMenu($this->menu_pid);
                }
                if (!$this->countChildrenNode($this->module_id)) {
                    $this->deleteNode($this->module_id);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
        DB::commit();
    }

    /**
     * * 使用indertAll创建数据，则必须使用这个方法回滚.
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function insertAllRollback($data)
    {
        $this->menu_pid = 3; //菜单的pid     默认为平台
        $this->module_id = 1; //模块id       默认为admin
        DB::beginTransaction();

        try {
            foreach ($data as $key => $datum) {
                $this->handleMenuNode($key, $datum);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
        DB::commit();
    }

    public function handleMenuNode($title, $node)
    {
        $menu = $this->queryMenu($title, 2, $this->menu_pid);
        if (empty($menu)) {
            throw new \Exception('回滚的菜单名不存在或者为空');
        }
        foreach ($node as $item) {
            $this->handleNode($item);
        }
        //删除菜单
        if (!$this->countMenuChildrenNode($menu->id)) {
            $this->deleteMenu($menu->id);
        }
    }

    /**
     * 处理 设置模块id.
     *
     * @param $moduleName
     *
     * @throws \Exception
     */
    public function setModuleId($moduleName)
    {
        $modu = $this->queryNode($moduleName, 1, 0);
        if (!empty($modu)) {
            $this->module_id = $modu->id;
        } else {
            throw new \Exception('未能查找到“'.$moduleName.'”模块名');
        }
    }

    /**
     * 设置top_menu menu_pid.
     *
     * @param $menuName
     *
     * @throws \Exception
     */
    public function setMenuPID($menuName)
    {
        $modu = $this->queryMenu($menuName, 1, 0);
        if (!empty($modu)) {
            $this->menu_pid = $modu->id;
        } else {
            throw new \Exception('未能查找到“'.$menuName.'”菜单名');
        }
    }

    /**
     * 处理 backend_menu.
     *
     * @param $menuName
     *
     * @throws \Exception
     */
    public function handleMenu($menuName)
    {
        $menu = $this->queryMenu($menuName, 2, $this->menu_pid);
        if (!empty($menu)) {
            $this->deleteMenu($menu->id);
        } else {
            throw new \Exception('2、未能查找到“'.$menuName.'”菜单名');
        }
    }

    /**
     *处理节点的逻辑关系.
     *
     * @param $data  这是一个数组
     *
     * @throws \Exception
     */
    public function handleNode($data)
    {
        if (empty($data)) {
            throw new \Exception('action handleNode() $data is null ');
        }
        //获取控制器
        $controller = $this->queryNode($data['controller'], 2, $this->module_id);
        if (empty($controller)) {
            //控制器不存在
            throw new \Exception('回滚错误，“'.$data['controller'].'”控制器不存在');
        }
        $node = $this->queryNode($data['name'], 3, $controller->id);
        //删除节点
        if (!empty($node)) {
            $this->deleteNode($node->id);
        } else {
            throw new \Exception('回滚错误，“'.$data['name'].'”节点方法不存在');
        }
        //删除控制器
        if (!$this->countChildrenNode($controller->id)) {
            $this->deleteNode($controller->id);
        }
    }

    /**查询menu
     * @param $title
     * @param $level
     * @param $pid
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function queryMenu($title, $level, $pid)
    {
        return DB::table('qs_menu')->where('title', $title)
            ->where('level', $level)
            ->where('pid', $pid)
            ->first();
    }

    /**
     * 查询menu.
     *
     * @param $name 菜单名
     * @param $level  菜单等级
     * @param $pid 菜单的父节点
     *
     * @throws \Exception
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function queryNode($name, $level, $pid)
    {
        if (empty($name)) {
            throw new \Exception('node name 为空');
        }

        return DB::table('qs_node')->where('name', $name)
            ->where('level', $level)
            ->where('pid', $pid)
            ->first();
    }

    /**查询id=pid的子节点数
     * @param $pid
     * @return int
     */
    public function countChildrenMenu($pid)
    {
        return DB::table('qs_menu')->where('pid', $pid)->count();
    }

    /**查询id=pid的子节点数
     * @param $pid
     * @return int
     */
    public function countChildrenNode($pid)
    {
        return DB::table('qs_node')->where('pid', $pid)->count();
    }

    /**查询菜单下是否有子节点
     * @param $pid
     * @return int
     */
    public function countMenuChildrenNode($menu_id)
    {
        return DB::table('qs_node')->where('menu_id', $menu_id)->count();
    }

    /**删除菜单
     * @param $id
     * @return int
     */
    public function deleteMenu($id)
    {
        return DB::table('qs_menu')->delete($id);
    }

    /**删除节点
     * @param $id 节点id
     * @return int
     */
    public function deleteNode($id)
    {
        return DB::table('qs_node')->delete($id);
    }

    public function resetInsertAll()
    {
        $this->menu_id = 0;
        $this->node_pid = 0;
        $this->menu_pid = 0;
        $this->module_id = 0;
    }
}
