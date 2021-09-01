<?php
namespace Qscmf\Builder\ListRightButton\Forbid;

use Qscmf\Builder\ListRightButton\ListRightButton;

class Forbid extends ListRightButton{

    public function build(array &$option, array $data, $listBuilder){
        $btn_type = [
            '0' => [
                'title' => '启用',
                'class' => 'success ajax-get confirm',
                'href' => U(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/resume',
                    array(
                        'ids' => '__data_id__'
                    )
                )
            ],
            '1' => [
                'title' => '禁用',
                'class' => 'warning ajax-get confirm',
                'href' => U(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/forbid',
                    array(
                        'ids' => '__data_id__'
                    )
                )
            ]
        ];

        $type = $btn_type[$data['status']];
        $option['attribute'] = $listBuilder->mergeAttr($type, is_array($option['attribute']) ? $option['attribute'] : []);
        return '';
    }
}