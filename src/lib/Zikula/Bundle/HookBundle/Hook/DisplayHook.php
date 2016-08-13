<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use Zikula\Core\UrlInterface;

/**
 * DisplayHook class.
 */
class DisplayHook extends Hook
{
    /**
     * Responses.
     *
     * @var DisplayHookResponse[]
     */
    private $responses = [];

    /**
     * The return url.
     *
     * @var UrlInterface
     */
    private $url;

    public function __construct($id, UrlInterface $url = null)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Add response.
     *
     * @param DisplayHookResponse $response
     * 
     * @return DisplayHookResponse
     */
    public function setResponse(DisplayHookResponse $response)
    {
        return $this->responses[$response->getArea()] = $response;
    }

    /**
     * Get responses.
     *
     * @return DisplayHookResponse[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Url getter.
     *
     * @return UrlInterface
     */
    public function getUrl()
    {
        return $this->url;
    }
}
