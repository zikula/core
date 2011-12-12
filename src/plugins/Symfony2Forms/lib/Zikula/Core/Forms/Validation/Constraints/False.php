<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class False implements Constraint
{
    public function isValid($value, Errors $error)
    {
        if($value == false || $value === 0 || $value === '0' || $value === null) {
            return true;
        }
        
        $error->addError('Must be false');
        return false;
    }
}

