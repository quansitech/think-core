<?php
namespace Qscmf\Builder\ListRightButton;

abstract class ListRightButton{

    public abstract function build(array &$option, array $data, $listBuilder);

}