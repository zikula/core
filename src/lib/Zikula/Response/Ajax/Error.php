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
abstract class Zikula_Response_Ajax_Error extends Zikula_Response_Ajax_AbstractMediatorBase
{
    /**
     * Constructor.
     *
     * @param mixed $message Response status/error message, may be string or array.
     * @param mixed $payload Payload.
     */
    public function __construct($message, $payload=null)
    {
        $this->messages = (array)$message;
        $this->payload = $payload;
        if ($this->newCsrfToken) {
            $this->authid = SecurityUtil::generateAuthKey(ModUtil::getName());
            $this->csrfToken = SecurityUtil::generateCsrfToken();
        }
    }

    /**
     * Generate system level payload.
     *
     * @return array
     */
    protected function generateCoreData()
    {
        $core = parent::generateCoreData();
        if (!isset($core['statusmsg']) || empty($core['statusmsg'])) {
            $core['statusmsg'] = __('An unknown error occurred');
        }

        return $core;
    }
}
