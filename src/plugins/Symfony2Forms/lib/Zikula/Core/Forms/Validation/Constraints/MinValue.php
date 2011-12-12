<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class MinValue implements Constraint
{
    private $min;
    
    public function __construct($min)
    {
        $this->min = $min;
    }
    
    public function isValid($value, Errors $error)
    {
        if($value === null) {
            return true;
        }
        
        if(!is_numeric($value)) {
            throw new \InvalidArgumentException('Unexpected argument type ' . gettype($value) . ', expected numeric');
        }
        
        if($value < $this->min) {
            $error->addError('Value must be ' . $this->min .' or more');
            return false;
        } else {
            return true;
        }
    }
}
