<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
    /**
     * Constructor.
     *
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param string $filename
     * @param int $lineno
     */
    public function __construct($message = 'Fatal Error!', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
    }
}
