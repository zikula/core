<?php

namespace SystemPlugin\Symfony2Forms\Validation\Builder;

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
