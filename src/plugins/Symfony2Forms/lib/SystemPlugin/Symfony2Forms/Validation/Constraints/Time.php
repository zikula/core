<?php

namespace SystemPlugin\Symfony2Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class Time implements Constraint
{
    const REGEX = '#^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$#';
    const MESSAGE = 'Must be a valid time';
    
    public function isValid($value, Errors $error)
    {
        if($value === '' || $value === null || $value instanceof \DateTime) {
            return true;
        }
        
        if(!preg_match(static::REGEX, $value)) {
            $error->addError(static::MESSAGE);
            return false;
        } else {
            return true;
        }
    }
}

