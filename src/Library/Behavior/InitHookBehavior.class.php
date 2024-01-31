<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
use Qscmf\Lib\DBCont;
use Think\Hook;

// 初始化钩子信息
class InitHookBehavior{

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        try{
            $qs_addons = C("QS_ADDONS", null, true);
            if($qs_addons) {
                $hooks_model = D('Hooks');
                $hooks = $hooks_model->getActiveHooks();

                foreach ($hooks as $hook) {
                    $addons = getHookAddons($hook['name']);
                    if ($addons) {
                        $map['status'] = DBCont::NORMAL_STATUS;
                        $map['name'] = array('IN', $addons);
                        $data = D('Addons')->where($map)->getField('name', true);
                        if ($data) {
                            Hook::add($hook['name'], array_map('get_addon_class', $data));
                        }
                    }
                }
            }
        }
        catch( \Exception $ex){

        }

    }
}