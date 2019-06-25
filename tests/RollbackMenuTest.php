<?php

namespace Larafortp\Tests;

use Illuminate\Support\Facades\DB;
use Larafortp\MenuGenerate;

class RollbackMenuTest extends TestCase
{
    //回滚InsertAll
    public function testRollbackInsertAll()
    {
        $hardyData = [
            '新闻中心'=> [
                [
                    'name'      => 'index',
                    'title'     => '新闻分类',
                    'controller'=> 'NewsCate',
                ],
                [
                    'name'      => 'index',
                    'title'     => '内容管理',
                    'controller'=> 'News',
                    'sort'      => 1,
                ],
            ],
            '佰特业务'=> [
                [
                    'name'      => 'YWindex',
                    'title'     => '封面及简介管理',
                    'controller'=> 'CoverStnopsis',
                    'sort'      => 1,
                ],
                [
                    'name'      => 'index',
                    'title'     => '服务对象管理',
                    'controller'=> 'ServiceCrowd',
                    'sort'      => 2,
                ],
                [
                    'name'      => 'index',
                    'title'     => '服务项目分类管理',
                    'controller'=> 'ServiceItems',
                    'sort'      => 3,
                ],
                [
                    'name'      => 'index',
                    'title'     => '服务项目内容',
                    'controller'=> 'ServiceContents',
                    'sort'      => 4,
                ],
            ],
        ];
        //第一次插入
        $menuGenerate = new MenuGenerate();
        $menuGenerate->insertAll($hardyData);
        //记录下“佰特业务”的id和其他信息
        $businessMenu = [
            'id'    => $menuGenerate->menu_id,
            'title' => '佰特业务',
            'level' => 2,
        ];
        //记录下佰特业务最后一条控制器
        $nodeController = [
            'id'      => $menuGenerate->node_pid,
            'name'    => 'ServiceContents',
            'title'   => 'ServiceContents',
            'level'   => 2,
            'pid'     => 1,
            'menu_id' => 0,
        ];
        //记录下佰特业务最后一条节点
        $nodeAction = [
            'name'    => 'index',
            'title'   => '服务项目内容',
            'level'   => 3,
            'pid'     => $menuGenerate->node_pid,
            'menu_id' => $menuGenerate->menu_id,
        ];
        //获取数据库的总数
        $befor_menu = DB::table('qs_menu')->count();
        $befor_node = DB::table('qs_node')->count();
        $data = [
            '首页'=> [
                [
                    'name'      => 'index',
                    'title'     => '首页轮播图',
                    'sort'      => 0,
                    'controller'=> 'IndexBanner',
                    'status'    => 1,
                ],
                [
                    'name'      => 'index',
                    'title'     => '首页信息配置',
                    'sort'      => 1,
                    'controller'=> 'IndexConfig',
                    'status'    => 1,
                ],
            ],
            '社会影响力'=> [
                [
                    'name'      => 'socialInfluence',
                    'title'     => '社会影响力封面简介管理',
                    'controller'=> 'CoverStnopsis',
                ],
                [
                    'name'      => 'index',
                    'title'     => '成长故事',
                    'sort'      => 0,
                    'controller'=> 'Story',
                    'status'    => 1,
                ],
            ],
        ];
        $menuGenerate = new MenuGenerate();
        $menuGenerate->insertAll($data);
        //确认数据库的变化
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        $this->assertNotEquals($befor_menu, $after_menu);
        $this->assertNotEquals($befor_node, $after_node);
        //确认回滚，数据条数不多不少
        $menuGenerate = new MenuGenerate();
        $menuGenerate->insertAllRollback($data);
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        $this->assertEquals($befor_menu, $after_menu);
        $this->assertEquals($befor_node, $after_node);
        //认证数据库中的数据是否存在
        //菜单
        $this->assertDatabaseHas('qs_menu', $businessMenu);
        //控制器
        $this->assertDatabaseHas('qs_node', $nodeController);
        //节点（方法）
        $this->assertDatabaseHas('qs_node', $nodeAction);
    }

    //回滚insertNavigationAllRollback
    public function testInsertNavigationAllRollback()
    {
        //插入多个平台
        $hardyData = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'admin2', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',
                            'title'     => '新闻分类',
                            'controller'=> 'NewsCate',
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '内容管理',
                            'controller'=> 'News',
                            'sort'      => 1,
                        ],
                    ],
                    '佰特业务'=> [
                        [
                            'name'      => 'YWindex',
                            'title'     => '封面及简介管理',
                            'controller'=> 'CoverStnopsis',
                            'sort'      => 1,
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '服务对象管理',
                            'controller'=> 'ServiceCrowd',
                            'sort'      => 2,
                        ],
                    ],
                ],
            ],
            [
                'title'      => '多个平台', //标题              (必填)
                'module'     => 'NewsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'top_menu'   => [
                    '佰特业务'=> [
                        [
                            'name'      => 'index',
                            'title'     => '佰特业务分类',
                            'controller'=> 'NewsCate',
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '内容管理',
                            'controller'=> 'News',
                            'sort'      => 1,
                        ],
                    ],
                ],
            ],
        ];
        //第一次插入
        $menuGenerate = new MenuGenerate();
        $menuGenerate->insertNavigationAll($hardyData);
        //记录下最后一个平台插入的信息
        $top_menu = [
            'id'   => $menuGenerate->menu_pid,
            'title'=> '多个平台',
            'level'=> 1,
            'type' => 'top_menu',
        ];
        //记录最后一个插入的菜单
        $businessMenu = [
            'id'    => $menuGenerate->menu_id,
            'title' => '佰特业务',
            'level' => 2,
        ];
        //记录最后一个插入的模块
        $Module = [
            'id'      => $menuGenerate->module_id,
            'name'    => 'NewsAdmin',
            'title'   => '后台管理',
            'level'   => 1,
            'pid'     => 0,
            'menu_id' => 0,
        ];
        //记录最后一个插入的控制器
        $nodeController = [
            'id'      => $menuGenerate->node_pid,
            'name'    => 'News',
            'title'   => 'News',
            'level'   => 2,
            'pid'     => $menuGenerate->module_id,
            'menu_id' => 0,
        ];
        //记录下佰特业务最后一条节点
        $nodeAction = [
            'name'    => 'index',
            'title'   => '内容管理',
            'level'   => 3,
            'pid'     => $menuGenerate->node_pid,
            'menu_id' => $menuGenerate->menu_id,
        ];
        //获取数据库的总数
        $befor_menu = DB::table('qs_menu')->count();
        $befor_node = DB::table('qs_node')->count();
        $data = [
            [
                'title'      => '多个平台', //标题              (必填)
                'module'     => 'NewsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'top_menu'   => [
                    '社会影响力'=> [
                        [
                            'name'      => 'socialInfluence',
                            'title'     => '社会影响力封面简介管理',
                            'controller'=> 'CoverStnopsis',
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '成长故事',
                            'sort'      => 0,
                            'controller'=> 'Story',
                            'status'    => 1,
                        ],
                    ],
                    '首页'=> [
                        [
                            'name'      => 'index',
                            'title'     => '首页轮播图',
                            'sort'      => 0,
                            'controller'=> 'IndexBanner',
                            'status'    => 1,
                        ],
                        [
                            'name'      => 'index',
                            'title'     => '首页信息配置',
                            'sort'      => 1,
                            'controller'=> 'IndexConfig',
                            'status'    => 1,
                        ],
                    ],
                ],
            ],
        ];
        $menuGenerate = new MenuGenerate();
        $menuGenerate->insertNavigationAll($data);
        //确认数据库的变化
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        $this->assertNotEquals($befor_menu, $after_menu);
        $this->assertNotEquals($befor_node, $after_node);
        //确认回滚，数据条数不多不少
        $menuGenerate = new MenuGenerate();
        $menuGenerate->insertNavigationAllRollback($data);
        $after_menu = DB::table('qs_menu')->count();
        $after_node = DB::table('qs_node')->count();
        $this->assertEquals($befor_menu, $after_menu);
        $this->assertEquals($befor_node, $after_node);
        //认证数据库中的数据是否存在
        //平台
        $this->assertDatabaseHas('qs_menu', $top_menu);
        //菜单
        $this->assertDatabaseHas('qs_menu', $businessMenu);
        //模块
        $this->assertDatabaseHas('qs_node', $Module);
        //控制器
        $this->assertDatabaseHas('qs_node', $nodeController);
        //节点（方法）
        $this->assertDatabaseHas('qs_node', $nodeAction);
    }
}
