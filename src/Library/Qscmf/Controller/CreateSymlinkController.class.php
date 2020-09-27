<?php
namespace Qscmf\Controller;

use Bootstrap\RegisterContainer;
use Illuminate\Filesystem\Filesystem;
use Think\Controller;

class CreateSymlinkController extends Controller{

    public function index(){
        $symLinks = RegisterContainer::getRegisterSymLinks();
        foreach($symLinks as $link => $source){
            self::makeIgnore($link);

            if(file_exists($link)){
                echo $link . ' exists' . PHP_EOL;
            }
            else{

                $relative_path = getRelativePath(normalizeRelativePath($source), normalizeRelativePath($link));
                $r = symlink($relative_path, $link);
                if($r){
                    echo 'create link: '. $link . ' => ' . $relative_path . PHP_EOL;
                }
                else{
                    echo 'create link: '. $link . ' failure !' . PHP_EOL;
                }
            }
        }
    }

    private function makeIgnore($link){
        $path_info = pathinfo($link);
        if(file_exists($path_info['dirname'] . '/.gitignore')){
            $content = file_get_contents($path_info['dirname'] . '/.gitignore');
            if(!preg_match('#(^/|[\n\r]+?/)' . $path_info['basename'] . '#', $content)){
                file_put_contents($path_info['dirname'] . '/.gitignore', PHP_EOL . '/' . $path_info['basename'], FILE_APPEND);
            }
        }
        else{
            file_put_contents($path_info['dirname'] . '/.gitignore', '/' . $path_info['basename']);
        }
    }
}