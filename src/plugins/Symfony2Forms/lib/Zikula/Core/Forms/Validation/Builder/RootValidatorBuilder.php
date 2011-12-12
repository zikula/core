<?php

namespace Zikula\Core\Forms\Validation\Builder;

/**
 *
 */
interface RootValidatorBuilder extends ValidatorBuilder
{
    /**
     * @return \SystemPlugin\Symfony2Forms\Validation\Validator
     */
    public function buildValidator();
}
