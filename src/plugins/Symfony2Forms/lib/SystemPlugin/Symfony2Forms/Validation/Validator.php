<?php

namespace SystemPlugin\Symfony2Forms\Validation;

/**
 *
 */
interface Validator
{
    public function validateArray(array $array);
    
    public function getFieldConstraints($field);
}

