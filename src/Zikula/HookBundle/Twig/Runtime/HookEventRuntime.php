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

namespace Zikula\Bundle\HookBundle\Twig\Runtime;

use Psr\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;

class HookEventRuntime implements RuntimeExtensionInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatchFilterHookEvent(string $content, string $filterEventName): string
    {
        if (\class_exists($filterEventName) && \is_subclass_of($filterEventName, FilterHookEvent::class)) {
            $hook = $this->eventDispatcher->dispatch((new $filterEventName())->setData($content));

            return $hook->getData();
        }

        return $content;
    }
}
