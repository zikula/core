<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionExpireListener implements EventSubscriberInterface
{
    private $installed;

    /**
     * SessionExpireListener constructor.
     * @param $installed
     */
    public function __construct($installed)
    {
        $this->installed = $installed;
    }

    public function onKernelRequestSessionExpire(GetResponseEvent $event)
    {
        if (!$this->installed) {
            return;
        }
        if ($event->getRequest()->hasSession() && $event->getRequest()->getSession()->get('session_expired', false)) {
            // Session has expired, display warning
            $response = new Response("Session expired.", 403);
            $response->legacy = true;
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestSessionExpire', 31]
            ]
        ];
    }
}
