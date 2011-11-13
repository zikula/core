<?php

namespace SystemPlugin\Symfony2Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class MaxLength implements Constraint
{
    private $max;
    private $charset;
    
    public function __construct($max, $charset)
    {
        $this->max = $max;
        $this->charset = $charset;
    }
    
    public function isValid($value, Errors $error)
    {
        if($value === null) {
            return true;
        }
        
        if(!is_scalar($value)) {
            throw new \InvalidArgumentException('Unexpected argument type ' . gettype($value) . ', expected string');
        }
        
        $length = function_exists('mb_strlen') ? mb_strlen($value, $this->charset) : strlen($value);
        
        if($length > $this->max) {
            $error->addError('Value must have ' . $this->max .' characters or less');
            return false;
        } else {
            return true;
        }
    }
}
