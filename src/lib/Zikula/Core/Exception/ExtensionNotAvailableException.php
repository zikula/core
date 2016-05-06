<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Exception;

/**
 * Class ExtensionNotAvailableException
 *
 * Describes a state where the Module|Theme|Plugin is installed but not available
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
