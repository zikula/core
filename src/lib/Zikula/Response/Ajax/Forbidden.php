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
class Zikula_Response_Ajax_Forbidden extends Zikula_Response_Ajax_Error
{
    /**
     * Constructor.
     *
     * @param mixed $data    Application data.
     * @param mixed $data    Response status/error message, may be string or array.
     * @param array $options Options.
     */
    public function __construct($data, $message = null, array $options = array())
    {
        $this->newCsrfToken = false;
        $this->responseCode = 403;
        parent::__construct($data, $message, $options);
    }
}
