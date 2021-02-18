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

namespace Zikula\Bundle\HookBundle\HookEventResponse;

final class DisplayHookEventResponse
{
    /* var string */
    private $listenerClassname;

    /* @var string */
    private $content;

    public function __construct(string $listenerClassname, string $content = '')
    {
        $this->listenerClassname = $listenerClassname;
        $this->content = $content;
    }

    public function getListenerClassName(): string
    {
        return $this->listenerClassname;
    }

    public function appendContent(string $content): void
    {
        $this->content .= $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
