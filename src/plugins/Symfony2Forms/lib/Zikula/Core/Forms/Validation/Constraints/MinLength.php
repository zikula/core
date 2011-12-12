<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class MinLength implements Constraint
{
    private $min;
    private $charset;
    
    public function __construct($min, $charset)
    {
        $this->min = $min;
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
        
        if($length < $this->min) {
            $error->addError('Value must have ' . $this->min .' characters or more');
            return false;
        } else {
            return true;
        }
    }
}
