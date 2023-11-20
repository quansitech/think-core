<?php


namespace Qscmf\Builder\ButtonType\Save;

use Qscmf\Builder\BaseBuilder;
use Qscmf\Builder\SubTableBuilder;

trait TargetFormTrait
{
    public function getSaveTargetForm(BaseBuilder | SubTableBuilder $builder):string{
        return $builder->getGid();
    }

}