<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class Email implements Constraint
{
    public function isValid($value, Errors $errors)
    {
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL) ? true : false;
        
        if(!$valid) {
            $errors->addError('Invalid E-Mail');
        }
        
        return $valid;
    }
}

