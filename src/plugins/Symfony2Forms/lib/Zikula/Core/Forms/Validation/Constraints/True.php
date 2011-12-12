<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class True implements Constraint
{
    public function isValid($value, Errors $error)
    {
        if($value == true || $value === 1 || $value === '1' || $value === null) {
            return true;
        }
        
        $error->addError('Must be true');
        return false;
    }
}

