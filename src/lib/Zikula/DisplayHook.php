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

/**
 * DisplayHook class.
 */
class Zikula_DisplayHook extends Zikula_AbstractHook
{
    /**
     * Responses.
     *
     * @var array
     */
    private $responses = array();

    /**
     * @var Zikula_AbstractController
     */
    private $controller;

    public function __construct($name, $id, Zikula_AbstractController $controller, Zikula_ModUrl $url = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->controller = $controller;
        $this->url = $url;
    }

    /**
     *
     * @return Zikula_AbstractController
     */
    public function getController()
    {
        return $this->controller;
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
     * @param mixed $data Data to be saved.
     *
     * @return Zikula_DisplayHook
     */
    public function getResponses()
    {
        return $this->responses;
    }
}
