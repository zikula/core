<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Helper;

use function Symfony\Component\String\s;

/**
 * Helper class for sanitizing route properties.
 */
class SanitizeHelper
{
    const SUFFIX_CONTROLLER = 'Controller';
    const SUFFIX_ACTION = 'Action';

    /**
     * Sanitizes the controller / type parameter.
     */
    public function sanitizeController(string $controllerName): string
    {
        return s($controllerName)->beforeLast(self::SUFFIX_CONTROLLER)->lower()->toString();
    }

    /**
     * Sanitizes the action / func parameter.
     */
    public function sanitizeAction(string $methodName): string
    {
        return lcfirst(s($methodName)->beforeLast(self::SUFFIX_ACTION)->toString());
    }
}
