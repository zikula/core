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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Exception\StaleCacheException;

class StaleCacheExceptionListener implements EventSubscriberInterface
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
            KernelEvents::EXCEPTION => [
                ['handleException', 1000]
            ]
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        if (!($event->getThrowable() instanceof StaleCacheException)) {
            return;
        }
        $uri = $event->getRequest()->getUri();
        $this->cacheClearer->clear('symfony.config');
        $event->setResponse(new RedirectResponse($uri));
        $event->stopPropagation();
    }
}
