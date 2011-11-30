<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Hook;
use Zikula\Common\HookManager\AbstractHook;

/**
 * DisplayHook class.
 */
class DisplayHook extends AbstractHook
{
    /**
     * Responses.
     *
     * @var array
     */
    private $responses = array();

    /**
     * The return url.
     *
     * @var ModUrl
     */
    private $url;

    public function __construct($name, $id, ModUrl $url = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Add response.
     *
     * @return mixed Data property.
     */
    public function setResponse(Zikula_Response_DisplayHook $response)
    {
        return $this->responses[$response->getArea()] = $response;
    }

    /**
     * Set data.
     *
     * @return DisplayHook
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Url getter.
     *
     * @return ModUrl
     */
    public function getUrl()
    {
        return $this->url;
    }
}
