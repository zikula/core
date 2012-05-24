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
 * Zikula_Exception_Redirect class.
 */
class Zikula_Exception_Redirect extends Zikula_Exception
{
    /**
     * Url.
     *
     * @var string
     */
    protected $url;

    /**
     * Redirect type.
     *
     * @var integer
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param string  $url  Url.
     * @param integer $type Default 302.
     */
    public function __construct($url, $type = 302)
    {
        $this->url = $url;
        $this->type = $type;
    }

    /**
     * Get Url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get redirect type.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }
}
