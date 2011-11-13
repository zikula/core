<?php

namespace SystemPlugin\Symfony2Forms\Validation\Builder;

use SystemPlugin\Symfony2Forms\Validation\Builder\Impl\RootValidatorBuilderImpl;

/**
 *
 */
class ValidatorBuilderFactory
{
    /**
     * @return RootValidatorBuilder
     */
    public static function create() 
    {
        return new RootValidatorBuilderImpl();
    }
}
