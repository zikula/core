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

namespace Zikula\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\CacheClearer;

class CacheClearSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheClearer $cacheClearer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['doClearCache', -1000],
        ];
    }

    public function doClearCache(TerminateEvent $event): void
    {
        $this->cacheClearer->doClear();
    }
}
