<?php
namespace Qscmf\Builder\ColumnType\Pictures;

use Qscmf\Builder\ColumnType\ColumnType;
use Think\View;

class Pictures extends ColumnType
{
    public function build(array &$option, array $data, $listBuilder)
    {
        $pic = explode(',', $data[$option['name']]);
        $images = [];
        foreach($pic as $v){
            if(!$v){
                continue;
            }
            switch ($option['value']){
                case 'oss':
                    $small_url = showFileUrl($v).'?x-oss-process=image/resize,m_fill,w_40,h_40';
                    break;
                case 'imageproxy':
                    $small_url = \Qscmf\Utils\Libs\Common::imageproxy('40x40', $v);
                    break;
                default:
                    $small_url = showFileUrl($v);
                    break;
            }
            $images[] = [
                'url' => showFileUrl($v),
                'small_url' => $small_url,
            ];
        }
        $view = new View();
        $view->assign('images', $images);
        $content = $view->fetch(__DIR__ . '/pictures.html');
        return $content;
    }
}