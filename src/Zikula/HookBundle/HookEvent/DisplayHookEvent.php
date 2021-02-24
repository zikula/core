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

/**
 * A DisplayHookEvent is most often dispatched within the UI (template) and can
 * be used to add content to the display.
 */
abstract class DisplayHookEvent extends HookEvent
{
    /* @var DisplayHookEventResponse[] */
    private $responses = [];

    final public function addResponse(DisplayHookEventResponse $response): self
    {
        if (isset($this->responses[$response->getListenerClassName()])) {
            $this->responses[$response->getListenerClassName()]->appendContent((string) $response);
        } else {
            $this->responses[$response->getListenerClassName()] = $response;
        }

        return $this;
    }

    final public function getResponses(): array
    {
        return $this->responses;
    }

    public function __toString(): string
    {
        return implode('', $this->responses);
    }
}
