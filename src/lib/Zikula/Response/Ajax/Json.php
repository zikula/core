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
class Zikula_Response_Ajax_Json extends Zikula_Response_Ajax_AbstractBase
{
    /**
     * Constructor.
     *
     * @param string $payload Payload data.
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Convert class to string.
     *
     * @return string
     */
    public function __toString()
    {
        header($this->createHttpResponseHeader());
        header('Content-type: application/json');

        return json_encode($this->payload);
    }

}
