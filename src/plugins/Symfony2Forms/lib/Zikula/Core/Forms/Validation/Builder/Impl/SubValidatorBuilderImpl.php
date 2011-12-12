<?php

namespace Zikula\Core\Forms\Validation\Builder\Impl;

use SystemPlugin\Symfony2Forms\Validation\Builder\SubValidatorBuilder;
use SystemPlugin\Symfony2Forms\Validation\Constraints;
use SystemPlugin\Symfony2Forms\Validation;

/**
 *
 */
class SubValidatorBuilderImpl implements SubValidatorBuilder
{
    const MODE_OBJECT = 1;
    const MODE_ARRAY = 2;
    
    private $fields;
    private $fieldValidator;
    private $mode;
    
    public function __construct($fieldValidator, $mode)
    {
        $this->fields = array();
        $this->fieldValidator = $fieldValidator;
        $this->mode = $mode;
    }
    
    public function buildValidator()
    {
        $constraint = new Constraints\Validator(
                new Validation\Impl\Validator($this->fields),
                $this->mode === self::MODE_ARRAY? 'is_array' : 'is_object',
                $this->mode === self::MODE_ARRAY? 'validateArray' : 'validateObject'
        );
        $this->fieldValidator->constraint($constraint);
        
        return $this->fieldValidator;
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

