<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class NotBlank implements Constraint
{
    public function isValid($value, Errors $error)
    {
        $valid = ($value === '' || $value === null) ? false : true;
        
        if(!$valid) {
            $error->addError('Field required');
        }
        
        return $valid;
    }
}

