<?php
namespace Qscmf\Controller;

use Bootstrap\RegisterContainer;
use Think\Controller;

class CreateSymlinkController extends Controller{

    public function index(){
        $symLinks = RegisterContainer::getRegisterSymLinks();
        foreach($symLinks as $link => $source){
            if(file_exists($link)){
                echo $link . ' exists' . PHP_EOL;
            }
            else{

                $r = symlink($source, $link);
                if($r){
                    echo 'create link: '. $link . ' => ' . $source . PHP_EOL;
                }
                else{
                    echo 'create link: '. $link . ' failure !' . PHP_EOL;
                }
            }
        }
    }
}