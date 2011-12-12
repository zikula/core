<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class NotNull implements Constraint
{
    public function isValid($value, Errors $error)
    {
        if($value !== null) {
            return true;
        }
        
        $error->addError('Can not be null');
        return false;
    }
}

