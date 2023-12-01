<?php
namespace Qscmf\Builder\ButtonType;

use Qscmf\Builder\ListBuilder;

abstract class ButtonType{

    abstract public function build(array &$option, ?ListBuilder $listBuilder);

}