<?php

namespace Zikula\Core\Forms\Validation\Builder;

/**
 *
 */
interface SubValidatorBuilder extends ValidatorBuilder
{
    /**
     * @return FieldValidatorBuilder
     */
    public function buildValidator();
}
