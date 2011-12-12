<?php

namespace Zikula\Core\Forms\Validation\Constraints;

/**
 *
 */
class Date extends Time
{
    const REGEX = '#^(\d{4})-(\d{2})-(\d{2})$#';
    const MESSAGE = 'Must be a valid date';
}

