<?php

namespace Qscmf\Builder\GenButton;

interface IGenButton
{
    public function getTableDataListKey();

    public function getPrimaryKey();

    public function registerButtonType();

    public function genOneButton($type, $attribute = null, $tips = '', $auth_node = '', $options = []);

    public function parseButtonList($button_list, &$data);

    public function getBtnDefClass();

    public function mergeAttr($def_attr, $cus_attr);

}