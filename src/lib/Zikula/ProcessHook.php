<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Zikula_ProcessHook extends Zikula_AbstractHook
{
    /**
     * Url container.
     *
     * @var Zikula_ModUrl
     */
    protected $url;

    public function __construct($name, $id, Zikula_ModUrl $url=null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
