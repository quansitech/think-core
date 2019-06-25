<?php

namespace Larafortp\Tests;

use Illuminate\Support\Facades\DB;
use Larafortp\MenuGenerate;

class MenuGenerateTopMenuModuleNodeTest extends TestCase
{
    /*
     * 统一捕获异常
     */
    public function captureException($arrData, $message)
    {
        $this->expectExceptionObject(new \Exception($message));
        $menu = new MenuGenerate();
        $menu->insertNavigationAll($arrData);
    }

    //多层级插入  -> MenuGenerate测试列表

    /**
     *     插入top_menu.
     */
    public function testAddTopMenuException()
    {
        $data = [
            [
                'title'      => '', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'News', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $this->captureException($data, '菜单title为空请检查');
    }

    /**
     * 创建模块.
     */
    public function testAddModuleException()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => '', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'News', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $this->captureException($data, '模块创建异常,模块名为空');
    }

    /**
     * 添加菜单.
     */
    public function testAddBackMenuException()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    ''=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'News', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $this->captureException($data, '菜单title为空请检查');
    }

    /**
     * 添加控制器.
     */
    public function testControllException()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> '', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $this->captureException($data, '控制器名为空请检查');
    }

    /**
     * 添加方法节点名称.
     */
    public function testNodeNameException()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => '',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $this->captureException($data, '方法名名为空请检查');
    }

    //插入空节点title
    public function testInsertNodeTitleExc()
    {

        //
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $this->captureException($data, '标题为空请检查');
    }

    //成功检查
    public function testNodeSuccess()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
        //节点
        $this->assertDatabaseHas('qs_node', ['name' => 'index', 'title' => '测试新闻中心', 'level' => 3, 'pid'=>$menuGener->node_pid, 'menu_id'=>$menuGener->menu_id]);
        //控制器
        $this->assertDatabaseHas('qs_node', ['id' => $menuGener->node_pid, 'name' => 'NewsController', 'title' => 'NewsController', 'level' => 2, 'pid'=>$menuGener->module_id, 'menu_id'=>0]);
        //菜单
        $this->assertDatabaseHas('qs_menu', ['id' => $menuGener->menu_id, 'title' => '新闻中心', 'level' => 2, 'pid'=>$menuGener->menu_pid]);
        //模块
        $this->assertDatabaseHas('qs_node', ['id' => $menuGener->module_id, 'name' => 'newsAdmin', 'title' => '后台管理', 'level' => 1, 'pid'=>0, 'menu_id'=>0]);
        //top_menu
        $this->assertDatabaseHas('qs_menu', ['id' => $menuGener->menu_pid, 'title' => '平台2', 'type'=>'top_menu', 'level' => 1, 'pid'=>0, 'module'=>'newsAdmin']);
    }

    /**
     * 认证重复插入.
     */
    public function testRepeatSuccess()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                    '测试模块'=> [
                        [
                            'name'      => 'index',
                            'title'     => '首页轮播图',
                            'sort'      => 0,
                            'controller'=> 'Index',
                            'status'    => 1,
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '新闻中心',
                            'sort'      => 1,
                            'controller'=> 'Index',
                            'status'    => 1,
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '集团机构',
                            'controller'=> 'Index',
                        ],
                    ],
                ],
            ],
        ];
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
        $befor_menu = DB::table('qs_menu')->count();
        $befor_node = DB::table('qs_node')->count();
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        $this->assertEquals($befor_menu, $after_menu);
        $this->assertEquals($befor_node, $after_node);
    }

    /**
     * 认证重复插入TopMenu以及新增一条node Controll 和一条node action.
     */
    public function testRepeatInsertTopMenu()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];
        $data2 = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                        ],
                        //新增
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '这是新的节点',    //（必填）'
                            'controller'=> 'News1', //（必填）
                        ],
                    ],
                ],
            ],
        ];
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
        $befor_menu = DB::table('qs_menu')->count();
        $befor_node = DB::table('qs_node')->count();
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data2);
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        $this->assertEquals($befor_menu, $after_menu);
        $this->assertTrue(($after_node - $befor_node) === 2);
    }

    /**
     * 认证重复插入BackMenu以及新增一条node action.
     */
    public function testRepeatInsertBackMenu()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                        ],
                    ],
                ],
            ],
        ];
        $data2 = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'top_menu'   => [
                    '这是新菜单'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                        ],
                    ],
                ],
            ],
        ];
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
        $befor_menu = DB::table('qs_menu')->count();
        $befor_node = DB::table('qs_node')->count();
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data2);
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        //新增一条菜单记录
        $this->assertTrue(($after_menu - $befor_menu) === 1);
        //新增一个节点，不会新增控制器，因为模块和控制器一样
        $this->assertTrue(($after_node - $befor_node) === 1);
    }

    /**
     * 认证重复插入BackMenu以及新增一条node Controll 和一条node action.
     */
    public function testRepeatInsertBackMenu2()
    {
        $data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                        ],
                    ],
                ],
            ],
        ];
        $data2 = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'top_menu'   => [
                    '这是新菜单'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'Controller', //（必填）
                        ],
                    ],
                ],
            ],
        ];
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
        $befor_menu = DB::table('qs_menu')->count();
        $befor_node = DB::table('qs_node')->count();
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data2);
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        //新增一条菜单记录
        $this->assertTrue(($after_menu - $befor_menu) === 1);
        //新增一个节点，不会新增控制器，因为模块和控制器一样
        $this->assertTrue(($after_node - $befor_node) === 2);
    }
}
