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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\CacheClearer;

class CacheClearListener implements EventSubscriberInterface
{
    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    public function __construct(
        CacheClearer $cacheClearer
    ) {
        $this->cacheClearer = $cacheClearer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => [
                ['doClearCache', -1000]
            ]
        ];
    }

    public function doClearCache(TerminateEvent $event): void
    {
        $this->cacheClearer->doClear();
    }
}
