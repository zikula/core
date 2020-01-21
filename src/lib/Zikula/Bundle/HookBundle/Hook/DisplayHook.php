<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use Zikula\Bundle\CoreBundle\UrlInterface;

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

    public function __construct(int $id = null, UrlInterface $url = null)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Add response.
     */
    public function setResponse(DisplayHookResponse $response): DisplayHookResponse
    {
        if (isset($this->responses[$response->getArea()])) {
            // if there are multiple reponses for the same area, append them together
            $existingContent = $this->responses[$response->getArea()]->__toString();
            $incomingContent = $response->__toString();
            $response = new DisplayHookResponse($response->getArea(), $existingContent . '<br>' . $incomingContent);
        }

        return $this->responses[$response->getArea()] = $response;
    }

    /**
     * Get responses.
     *
     * @return DisplayHookResponse[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getUrl(): UrlInterface
    {
        return $this->url;
    }
}
