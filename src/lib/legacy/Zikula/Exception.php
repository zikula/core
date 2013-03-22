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
 * Zikula_Exception class.
 */
class Zikula_Exception extends Exception
{
    /**
     * Debug value.
     *
     * @var mixed
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param string  $message Default ''.
     * @param integer $code    Code.
     * @param mixed   $debug   Debug.
     */
    public function __construct($message='', $code=0, $debug=null)
    {
        parent::__construct($message, $code);
        $this->debug = $debug;
    }

    /**
     * Get debug.
     *
     * @return array.
     */
    public function getDebug()
    {
        return (array)$this->debug;
    }
}
