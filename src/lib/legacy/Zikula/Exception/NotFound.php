<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Exception
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Exception_NotFound class.
 */
class Zikula_Exception_NotFound extends Zikula_Exception
{
    /**
     * Constructor.
     *
     * @param string  $message Message default = ''.
     * @param integer $code    Code default = 404.
     * @param mixed   $debug   Debug default = null.
     */
    public function __construct($message='', $code=404, $debug=null)
    {
        if (empty($message)) {
            $message = __('The requested page could not be found or is not currently accessible.');
        }
        parent::__construct($message, $code, $debug);
    }
}
