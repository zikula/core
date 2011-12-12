<?php

namespace Zikula\Core\Forms\Validation;

/**
 */
interface Constraint
{
    public function isValid($value, Errors $errors);
}

