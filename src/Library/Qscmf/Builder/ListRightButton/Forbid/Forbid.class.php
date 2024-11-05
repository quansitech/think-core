<?php
namespace Qscmf\Builder\ListRightButton\Forbid;

use AntdAdmin\Component\ColumnType\RuleType\Eq;
use AntdAdmin\Component\Table\ColumnType\ActionType\Link;
use Qscmf\Builder\Antd\BuilderAdapter\ListAdapter\IAntdTableRightBtn;
use Qscmf\Builder\ListRightButton\ListRightButton;

class Forbid extends ListRightButton implements IAntdTableRightBtn
{

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

    public function tableRightBtnAntdRender($options, $listBuilder): array
    {
        $forbidLink = new Link('禁用');
        $forbidLink->addShowRules('status', [new Eq(1)])
            ->request('put', U('forbid'), ['ids' => '__id__']);
        $resumeLink = new Link('启用');
        $resumeLink->addShowRules('status', [new Eq(0)])
            ->request('put', U('resume'), ['ids' => '__id__']);
        return [$forbidLink, $resumeLink];
    }
}