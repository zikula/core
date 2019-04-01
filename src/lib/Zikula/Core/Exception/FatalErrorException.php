<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Exception;

use Symfony\Component\Debug\Exception\FatalErrorException as SymfonyFatalErrorException;

/**
 * Class FatalErrorException
 */
class FatalErrorException extends SymfonyFatalErrorException
{
    public function __construct(string $message = 'Fatal Error!', int $code = 0, int $severity = 1, string $filename = __FILE__, int $lineno = __LINE__)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
    }
}
