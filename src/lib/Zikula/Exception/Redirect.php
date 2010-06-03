<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Zikula_Exception_Redirect extends Zikula_Exception
{
    protected $url;
    protected $type;

    public function __construct($url, $type = 302)
    {
        $this->url = $url;
        $this->type = $type;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return $this->type;
    }
}