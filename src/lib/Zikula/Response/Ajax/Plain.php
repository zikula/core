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
class Zikula_Response_Ajax_Plain extends Zikula_Response_Ajax_Base
{
    /**
     * Reponse data.
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param mixed $data    Application data.
     * @param array $options Options (unused).
     */
    public function __construct($data, array $options = array())
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Convert class to string.
     *
     * @return void
     */
    public function __toString()
    {
        header($this->createHttpResponseHeader());
        header('Content-type: text/html');
        echo $this->data;
    }

}
