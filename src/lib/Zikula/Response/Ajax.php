<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Ajax class.
 */
class Zikula_Response_Ajax extends Zikula_Response_Ajax_AbstractMediatorBase
{
    /**
     * Constructor.
     *
     * @param mixed $payload Application data.
     * @param mixed $message Response status/error message, may be string or array.
     * @param array $options Options.
     */
    public function __construct($payload, $message = null, array $options = array())
    {
        $this->payload = $payload;
        $this->messages = (array)$message;
        $this->options = $options;
        if ($this->newCsrfToken) {
            if (System::isLegacyMode()) {
                $this->authid = SecurityUtil::generateAuthKey(ModUtil::getName());
            }
            $this->csrfToken = SecurityUtil::generateCsrfToken();
        }
    }
}
