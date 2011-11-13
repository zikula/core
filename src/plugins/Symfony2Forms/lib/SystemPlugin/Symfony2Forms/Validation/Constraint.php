<?php

namespace SystemPlugin\Symfony2Forms\Validation;

/**
 */
interface Constraint
{
    public function isValid($value, Errors $errors);
}

