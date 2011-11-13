<?php

namespace SystemPlugin\Symfony2Forms\Validation\Builder\Impl;

use SystemPlugin\Symfony2Forms\Validation\Builder\RootValidatorBuilder;
use SystemPlugin\Symfony2Forms\Validation;

/**
 *
 */
class RootValidatorBuilderImpl implements RootValidatorBuilder
{
    private $fields;
    
    public function __construct()
    {
        $this->fields = array();
    }
    
    public function buildValidator()
    {
        return new Validation\Impl\Validator($this->fields);
    }

    public function forField($name)
    {
        return new FieldValidatorBuilderImpl($name, $this);
    }
    
    public function addConstraints($field, array $constraints) 
    {
        $this->fields[$field] = $constraints;
    }
}

