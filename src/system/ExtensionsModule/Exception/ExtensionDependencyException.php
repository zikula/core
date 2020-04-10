<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Exception;

use Exception;

/**
 * Class ExtensionDependencyException
 *
 * Describes a state where the Module|Theme|Plugin has a dependency that is not available.
 */
class ExtensionDependencyException extends Exception
{
    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
