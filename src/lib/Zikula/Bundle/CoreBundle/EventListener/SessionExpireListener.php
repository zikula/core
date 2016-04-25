<?php
/**
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
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequestSessionExpire', 31),
            )
        );
    }
}
