<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\HookEvent;

use Zikula\Bundle\HookBundle\HookEventResponse\DisplayHookEventResponse;

abstract class DisplayHookEvent extends HookEvent
{
    /**
     * @var DisplayHookEventResponse[]
     */
    private $responses = [];

    public function addResponse(DisplayHookEventResponse $response): void
    {
        $responseClass = get_class($response);
        if (isset($this->responses[$responseClass])) {
            $this->responses[$responseClass]->appendContent($response);
        } else {
            $this->responses[$responseClass] = $response;
        }
    }

    public function getResponses(): array
    {
        return $this->responses;
    }
}
