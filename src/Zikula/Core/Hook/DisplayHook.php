<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookDispatcher
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Hook;

use Zikula\Component\HookDispatcher\Hook;
use Zikula\Core\ModUrl;
use Zikula\Core\Hook\Response\DisplayHookResponse;

/**
 * DisplayHook class.
 */
class DisplayHook extends Hook
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

    public function __construct($id, ModUrl $url = null)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Add response.
     *
     * @param DisplayHookResponse $response
     *
     * @return mixed Data property.
     */
    public function setResponse(DisplayHookResponse $response)
    {
        return $this->responses[$response->getArea()] = $response;
    }

    /**
     * Set data.
     *
     * @return array of DisplayHookResponse
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
