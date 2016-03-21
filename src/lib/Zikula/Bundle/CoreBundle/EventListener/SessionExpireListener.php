<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionExpireListener implements EventSubscriberInterface
{
    public function onKernelRequestSessionExpire(GetResponseEvent $event)
    {
        if (\SessionUtil::hasExpired()) {
            // Session has expired, display warning
            $response = new Response(\ModUtil::apiFunc('ZikulaUsersModule', 'user', 'expiredsession'), 403);
            $this->setResponse($event, $response);
        }
    }

    private function setResponse(GetResponseEvent $event, Response $response)
    {
        $response->legacy = true;
        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestSessionExpire', 31],
            ]
        ];
    }
}
