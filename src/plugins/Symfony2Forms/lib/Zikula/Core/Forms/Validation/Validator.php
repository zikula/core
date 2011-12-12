<?php

namespace Zikula\Core\Forms\Validation;

/**
 *
 */
interface Validator
{
    public function validateArray(array $array);
    
    public function getFieldConstraints($field);
}

