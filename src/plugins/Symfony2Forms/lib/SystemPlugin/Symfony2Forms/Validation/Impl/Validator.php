<?php

namespace SystemPlugin\Symfony2Forms\Validation\Impl;

use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class Validator implements \SystemPlugin\Symfony2Forms\Validation\Validator
{
    private $fields;
    
    public function __construct($fields)
    {
        $this->fields = $fields;
    }
    
    public function validateArray(array $array)
    {
        $valid = true;
        $errors = new Errors();
        
        foreach($this->fields as $field => $constraints) {
            foreach($constraints as $constraint) {
                if(!$constraint->isValid($array[$field], $errors->getField($field))) {
                    $valid = false;
                }
            }
        }
        
        return $valid? null : $errors;
    }

    public function getFieldConstraints($field)
    {
        return (isset($this->fields[$field]))? $this->fields[$field] : null;
    }
}

