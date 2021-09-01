<?php

namespace Qscmf\Builder\ListRightButton;

interface RightButtonInterface
{
    public function getTableDataListKey();

    public function getPrimaryKey();

    public function registerRightButtonType();

    public function addRightButton($type, $attribute = null, $tips = '', $auth_node = '', $options = []);

    public function getRightBtnDefClass();

    public function mergeAttr($def_attr, $cus_attr);

}