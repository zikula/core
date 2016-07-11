<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Exception;

/**
 * Class ExtensionDependencyException
 *
 * Describes a state where the Module|Theme|Plugin has a dependency that is not available.
 */
class ExtensionDependencyException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
