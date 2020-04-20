<?php
namespace Bootstrap;

class RegisterContainer{
    static protected $form_item = [];
    static protected $extend_controllers = [];
    static protected $sym_links = [];
    static protected $list_topbutton = [];
    static protected $list_search_type = [];
    static protected $list_right_button = [];
    static protected $migrate_paths = [];
    static protected $head_js = [];
    static protected $list_column_type = [];

    static public function registerListColumnType($type, $type_cls){
        self::$list_column_type[$type] = $type_cls;
    }

    static public function getListColumnType(){
        return self::$list_column_type;
    }

    static public function registerHeadJs($srcs, $async = false){
        foreach((array)$srcs as $src){
            self::$head_js[] = [
                'src' => $src,
                'async' => $async
            ];
        }
    }

    static public function getHeadJs(){
        return self::$head_js;
    }

    static public function registerMigration($paths){
        foreach((array)$paths as $path){
            self::$migrate_paths[] = $path;
        }
    }

    static public function getRegisterMigratePaths(){
        return self::$migrate_paths;
    }

    static public function registerListRightButtonType($type, $type_cls){
        self::$list_right_button[$type] = $type_cls;
    }

    static public function getListRightButtonType(){
        return self::$list_right_button;
    }

    static public function registerListSearchType($type, $type_cls){
        self::$list_search_type[$type] = $type_cls;
    }

    static public function getListSearchType(){
        return self::$list_search_type;
    }

    /**
     * @param $link_path 软连接文件地址
     * @param $source_path 源文件地址
     */
    static public function registerSymLink($link_path, $source_path){
        if(isset(self::$sym_links[$link_path])){
            E('存在冲突软连接');
        }

        self::$sym_links[$link_path] = $source_path;
    }

    static public function getRegisterSymLinks(){
        return self::$sym_links;
    }

    static public function registerListTopButton($type, $type_cls){
        self::$list_topbutton[$type] = $type_cls;
    }

    static public function getListTopButtons(){
        return self::$list_topbutton;
    }

    static public function registerFormItem($type, $type_cls){
        self::$form_item[$type] = $type_cls;
    }

    static public function getFormItems(){
        return self::$form_item;
    }

    static public function registerController($module_name, $controller_name, $controller_cls){
//        $user_define_module = self::getUserDefineModule();
//        if($user_define_module){
//            $module_name = $user_define_module;
//        }

        if(self::existRegisterController($module_name, $controller_name)){
            E('注册控制器存在冲突');
        }

        if(class_exists($controller_cls)){
            self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)] = $controller_cls;
        }
        else{
            E('需要注册的类不存在');
        }
    }

//    static private function getUserDefineModule(){
//        static $provider_map = [];
//        $back_trace = debug_backtrace();
//
//        if(!$provider_map){
//            $packages = Context::getRegProvider();
//            $provider_map = collect($packages)->map(function($item, $key){
//                return [ $item['providers'][0] => $key];
//            })->collapse()->all();
//        }
//        foreach($back_trace as $trace){
//            $re = collect($provider_map)->filter(function($item, $key)use($trace){
//                return $key == ltrim($trace['class'], '\\');
//            })->all();
//
//            if($re){
//                $package = $re[ltrim($trace['class'], '\\')];
//                break;
//            }
//        }
//
//        if(!$package){
//            return null;
//        }
//
//        return packageConfig($package, 'module');
//    }

    static public function getRegisterController($module_name, $controller_name){
        return self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)];
    }

    static public function existRegisterController($module_name, $controller_name){
        return isset(self::$extend_controllers[strtolower($module_name)][strtolower($controller_name)]);
    }

    static public function existRegisterModule($module_name){
        return isset(self::$extend_controllers[strtolower($module_name)]);
    }
}