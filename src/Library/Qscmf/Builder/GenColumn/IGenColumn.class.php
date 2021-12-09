<?php

namespace Qscmf\Builder\GenColumn;

interface IGenColumn
{

    public function registerColumnType();

    public function genOneColumnOpt($name, $title, $type = null, $value = '', $editable = false, $tip = '',
                                    $th_extra_attr = '', $td_extra_attr = '', $auth_node = '', $extra_attr = '', $extra_class = '');

    public function buildOneColumnItem(&$column, &$data);

    public function getTableDataListKey();

}