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

namespace Zikula\CoreBundle\EventSubscriber;

use Gedmo\IpTraceable\IpTraceableListener as IpTraceable;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * IpTraceable subscriber to provide the current users IP address.
 *
 * Workaround until https://github.com/stof/StofDoctrineExtensionsBundle/pull/233
 */
class IpTraceableSubscriber
{
    public function __construct(
        private readonly IpTraceable $ipTraceableListener
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * Set the current users IP address.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $ip = $event->getRequest()->getClientIp();
        if (!empty($ip)) {
            $this->ipTraceableListener->setIpValue($ip);
        }
    }
}
