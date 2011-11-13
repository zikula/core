<?php

namespace SystemPlugin\Symfony2Forms\Validation\Form;

use Symfony\Component\Form\AbstractExtension;
use SystemPlugin\Symfony2Forms\Validation\Form\Type\FieldTypeValidatorExtension;

/**
 *
 */
class ValidatorExtension extends AbstractExtension
{
    protected function loadTypeExtensions()
    {
        return array(new FieldTypeValidatorExtension());
    }
    
    protected function loadTypeGuesser()
    {
        return new ValidatorTypeGuesser();
    }
}

