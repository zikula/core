<?php

namespace SystemPlugin\Symfony2Forms\Validation\Builder;

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
