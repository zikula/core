<?php

namespace SystemPlugin\Symfony2Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class Type implements Constraint
{
    private $type;
    
    public function __construct($type)
    {
        if($type == 'boolean') {
            $type = 'bool';
        }
        
        $this->type = $type;
    }
    
    public function isValid($value, Errors $error)
    {
        if($value === null) {
            return true;
        }
        
        $function = 'is_' . $this->type;
        
        if(is_callable($function) && call_user_func($function, $value)) {
            return true;
        } else if($value instanceof $this->type) {
            return true;
        } else {
            $error->addError('The type of this value must be: ' . $this->type);
            return false;
        }
    }
}
