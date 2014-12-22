<?php
/**
 * Copyright 2014 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Exception;

/**
 * Class ExtensionNotAvailableException
 *
 * Describes a state where the Module|Theme|Plugin is installed but not available
 *
 * @package Zikula\Core\Exception
 */
class ExtensionNotAvailableException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0)
    {
        if (empty($message)) {
            $message = __f("The requested extension [%s%] is currently unavailable.", __NAMESPACE__);
        }
        parent::__construct($message, $code);
    }
} 