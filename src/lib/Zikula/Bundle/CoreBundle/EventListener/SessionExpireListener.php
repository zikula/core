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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionExpireListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $installed;

    public function __construct(bool $installed)
    {
        $this->installed = $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestSessionExpire', 31]
            ]
        ];
    }

    public function onKernelRequestSessionExpire(RequestEvent $event): void
    {
        if (!$this->installed) {
            return;
        }
        $session = null !== $event->getRequest() && $event->getRequest()->hasSession()
            && null !== $event->getRequest()->getSession() ? $event->getRequest()->getSession() : null;
        if (null !== $session && $session->get('session_expired', false)) {
            // Session has expired, display warning
            $response = new Response('Session expired.', 403);
            $event->setResponse($response);
        }
    }
}
