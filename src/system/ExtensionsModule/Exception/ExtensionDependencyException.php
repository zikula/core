<?php
/**
 * Copyright 2016 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
