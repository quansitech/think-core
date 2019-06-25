<?php

namespace Larafortp\Tests;

use Larafortp\MenuGenerate;

class RollbackMenuExceptionTest extends TestCase
{
    public function hardyData()
    {
        return [
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
        ];
    }

    /*
     * 统一捕获insertNavigationAllRollback异常
     */
    public function captureException($data, $message)
    {
        $this->expectExceptionObject(new \Exception($message));
        $menuGener = new MenuGenerate();
        $menuGener->insertAllRollback($data);
    }

    public function insert()
    {
        $menuGener = new MenuGenerate();
        $menuGener->insertAll($this->hardyData());
    }

    //回滚InsertAll
    public function testRollbackNullMenu()
    {
        $this->insert();
        //回滚菜单异常
        $data = [
            ''=> [
                [
                    'name'      => 'index',
                    'title'     => '新闻分类',
                    'controller'=> 'NewsCate',
                ],
            ],
        ];
        $this->captureException($data, '回滚的菜单名不存在或者为空');
    }

    //回滚不存在的菜单名
    public function testRollbackNotMenu()
    {
        $this->insert();
        //回滚菜单异常
        $data = [
            '集团机构'=> [
                [
                    'name'      => 'index',
                    'title'     => '新闻分类',
                    'controller'=> 'NewsCate',
                ],
            ],
        ];
        //集团机构不在数据库中
        $this->captureException($data, '回滚的菜单名不存在或者为空');
    }

    //空控制器
    public function testRollbackNullController()
    {
        $this->insert();
        //回滚节点名空异常
        $hardyData = [
            '新闻中心'=> [
                [
                    'name'      => 'index',
                    'title'     => '新闻分类',
                    'controller'=> '',
                ],
            ],
        ];
        $this->captureException($hardyData, 'node name 为空');
    }

    //不存在的控制器
    public function testRollbackNotController()
    {
        $this->insert();
        //回滚节点名空异常
        $hardyData = [
            '新闻中心'=> [
                [
                    'name'      => 'index',
                    'title'     => '新闻分类',
                    'controller'=> 'NewsNotControll',
                ],
            ],
        ];
        //NewsNotControll不是插入的控制器
        $this->captureException($hardyData, '回滚错误，“NewsNotControll”控制器不存在');
    }

    //空节点
    public function testRollbackNullNodeAction()
    {
        $this->insert();
        //回滚节点名空异常
        $hardyData = [
            '新闻中心'=> [
                [
                    'name'      => '',
                    'title'     => '新闻分类',
                    'controller'=> 'NewsCate',
                ],
            ],
        ];
        $this->captureException($hardyData, 'node name 为空');
    }

    //不存在的节点
    public function testRollbackNotNodeAction()
    {
        $this->insert();
        //回滚节点名空异常
        $hardyData = [
            '新闻中心'=> [
                [
                    'name'      => 'inedx1',
                    'title'     => '新闻分类',
                    'controller'=> 'NewsCate',
                ],
            ],
        ];
        //回滚不存在的index1
        $this->captureException($hardyData, '回滚错误，“inedx1”节点方法不存在');
    }

    //插入多层级菜单
    public function TopMenuData()
    {
        return [
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
    }

    /*
     * 回滚insertNavigationAllRollback
     * 统一捕获insertNavigationAllRollback异常
     */
    public function captureNavException($data, $message)
    {
        $this->expectExceptionObject(new \Exception($message));
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAllRollback($data);
    }

    public function insertNavigationAll($data)
    {
        $menuGener = new MenuGenerate();
        $menuGener->insertNavigationAll($data);
    }

    //菜单为空
    public function testInsertNavigationAllRollbackTopMenu()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['title'] = '';
        $this->captureNavException($data, '未能查找到“”菜单名');
    }

    //不存在菜单
    public function testInsertNavigationAllNotTopMenu()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['title'] = '新菜单名';
        $this->captureNavException($data, '未能查找到“新菜单名”菜单名');
    }

    //模块名为空
    public function testInsertNavigationAllRollbackModule()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['module'] = '';
        $this->captureNavException($data, 'node name 为空');
    }

    //不存在模块名
    public function testInsertNavigationAllNotModule()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['module'] = 'NotAdmin';
        $this->captureNavException($data, '未能查找到“NotAdmin”模块名');
    }

    //控制器为空
    public function testInsertNavigationAllRollbackController()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['top_menu']['新闻中心'][0]['controller'] = '';
        $this->captureNavException($data, 'node name 为空');
    }

    //不存在控制器名
    public function testInsertNavigationAllNotController()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['top_menu']['新闻中心'][0]['controller'] = 'NotController';
        $this->captureNavException($data, '回滚错误，“NotController”控制器不存在');
    }

    //节点名为空
    public function testInsertNavigationAllRollbackAction()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['top_menu']['新闻中心'][0]['name'] = '';
        $this->captureNavException($data, 'node name 为空');
    }

    //不存在节点名
    public function testInsertNavigationAllNotAction()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['top_menu']['新闻中心'][0]['name'] = 'NotAction';
        $this->captureNavException($data, '回滚错误，“NotAction”节点方法不存在');
    }

    //菜单名为空
    public function testInsertNavigationAllRollbackMenu()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $data[0]['top_menu']['新闻中心'][0]['name'] = '';
        $this->captureNavException($this->BackMenu(1), '回滚的菜单名不存在或者为空');
    }

    //不存在菜单名
    public function testInsertNavigationAllNotMenu()
    {
        $data = $this->TopMenuData();
        $this->insertNavigationAll($data);
        $this->captureNavException($this->BackMenu(), '回滚的菜单名不存在或者为空');
    }

    public function BackMenu($num = 2)
    {
        if ($num === 1) {
            return [
                [
                    'title'      => '平台2', //标题              (必填)
                    'module'     => 'admin2', //模块英文名        (必填)
                    'module_name'=> '后台管理', //模块中文名   (必填)
                    'top_menu'   => [
                        ''=> [
                            [
                                'name'      => 'index',
                                'title'     => '新闻分类',
                                'controller'=> 'NewsCate',
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            return [
                [
                    'title'      => '平台2', //标题              (必填)
                    'module'     => 'admin2', //模块英文名        (必填)
                    'module_name'=> '后台管理', //模块中文名   (必填)
                    'top_menu'   => [
                        '不存在菜单名'=> [
                            [
                                'name'      => 'index',
                                'title'     => '新闻分类',
                                'controller'=> 'NewsCate',
                            ],
                        ],
                    ],
                ],
            ];
        }
    }
}
