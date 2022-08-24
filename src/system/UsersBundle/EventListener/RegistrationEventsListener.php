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

namespace Zikula\UsersBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\UsersBundle\Event\RegistrationPostSuccessEvent;
use Zikula\UsersBundle\Helper\MailHelper;

class RegistrationEventsListener implements EventSubscriberInterface
{
    public function __construct(private readonly RequestStack $requestStack, private readonly MailHelper $mailHelper)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            RegistrationPostSuccessEvent::class => ['sendRegistrationEmail']
        ];
    }

    public function sendRegistrationEmail(RegistrationPostSuccessEvent $event): void
    {
        $userEntity = $event->getUser();
        $notificationErrors = $this->mailHelper->createAndSendRegistrationMail($userEntity);
        if (empty($notificationErrors)) {
            return;
        }
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || !$request->hasSession() || null === $request->getSession()) {
            return;
        }
        $request->getSession()->getFlashBag()->add('error', implode('<br />', $notificationErrors));
    }
}
