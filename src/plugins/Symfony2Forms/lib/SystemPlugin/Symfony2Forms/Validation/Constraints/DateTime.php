<?php

namespace SystemPlugin\Symfony2Forms\Validation\Constraints;

/**
 *
 */
class DateTime extends Time
{
    const REGEX = '#^(\d{4})-(\d{2})-(\d{2}) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$#';
    const MESSAGE = 'Must be a valid datetime';
}

