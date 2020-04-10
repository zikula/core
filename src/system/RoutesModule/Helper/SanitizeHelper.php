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

/**
 * Helper class for sanitizing route properties.
 */
class SanitizeHelper
{
    /**
     * Sanitizes the controller / type parameter.
     */
    public function sanitizeController(string $controllerName): array
    {
        if ('Controller' !== substr($controllerName, -10)) {
            $type = $controllerName;
            $controllerName .= 'Controller';
        } else {
            $type = substr($controllerName, 0, -10);
        }

        $type = strtolower($type);
        $controllerName = ucfirst($controllerName);

        return [$controllerName, $type];
    }

    /**
     * Sanitizes the action / func parameter.
     */
    public function sanitizeAction(string $methodName): array
    {
        if ('Action' !== substr($methodName, -6)) {
            $methodName .= 'Action';
        }

        $methodName = ucfirst($methodName);
        $func = lcfirst(substr($methodName, 0, -6));

        return [$methodName, $func];
    }
}
