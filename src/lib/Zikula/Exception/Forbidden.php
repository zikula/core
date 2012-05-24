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
 * Zikula_Exception_Forbidden class.
 */
class Zikula_Exception_Forbidden extends Zikula_Exception
{
    /**
     * Constructor.
     *
     * @param string  $message Message default = ''.
     * @param integer $code    Code default = 0.
     * @param mixed   $debug   Debug default = null.
     */
    public function __construct($message='', $code=0, $debug=null)
    {
        if (empty($message)) {
            $message = __('Sorry! You have not been granted access to this page.');
        }
        parent::__construct($message, $code, $debug);
    }
}
