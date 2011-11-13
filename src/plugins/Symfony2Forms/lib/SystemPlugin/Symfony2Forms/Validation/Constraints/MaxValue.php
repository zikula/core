<?php

namespace SystemPlugin\Symfony2Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class MaxValue implements Constraint
{
    private $max;
    
    public function __construct($max)
    {
        $this->max = $max;
    }
    
    public function isValid($value, Errors $error)
    {
        if($value === null) {
            return true;
        }
        
        if(!is_numeric($value)) {
            throw new \InvalidArgumentException('Unexpected argument type ' . gettype($value) . ', expected numeric');
        }
        
        if($value > $this->max) {
            $error->addError('Value must be ' . $this->max .' or less');
            return false;
        } else {
            return true;
        }
    }
}
