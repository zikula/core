<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class Validator implements Constraint
{
    private $validator;
    private $typeCheckFunction;
    private $validateMethod;
    
    public function __construct(\SystemPlugin\Symfony2Forms\Validation\Validator $validator, $typeCheckFunction, $validateMethod)
    {
        $this->validator = $validator;
        $this->typeCheckFunction = $typeCheckFunction;
        $this->validateMethod = $validateMethod;
    }
    
    public function isValid($value, Errors $error)
    {
        if($value === null) {
            return true;
        }
        
        if(!call_user_func($this->typeCheckFunction, $value)) {
            $type = substr($this->typeCheckFunction, strpos($this->typeCheckFunction, '_') + 1);
            throw new \InvalidArgumentException('Unexpected argument type ' . gettype($value) . ', expected ' . $type);
        }
        
        $errors = call_user_func(array($this->validator, $this->validateMethod), $value);
        
        if($errors) {
            $error->setFromErrorsObject($errors);
            return false;
        } else {
            return true;
        }
    }
}

