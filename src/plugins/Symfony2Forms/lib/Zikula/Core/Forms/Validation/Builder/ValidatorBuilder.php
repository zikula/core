<?php

namespace Zikula\Core\Forms\Validation\Builder;

/**
 *
 */
interface ValidatorBuilder
{
    /**
     * @return FieldValidatorBuilder
     */
    public function forField($name);
}
