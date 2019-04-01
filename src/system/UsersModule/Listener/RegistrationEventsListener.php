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

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Helper\MailHelper;
use Zikula\UsersModule\RegistrationEvents;

class RegistrationEventsListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var MailHelper
     */
    private $mailHelper;

    public function __construct(RequestStack $requestStack, MailHelper $mailHelper)
    {
        $this->requestStack = $requestStack;
        $this->mailHelper = $mailHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            RegistrationEvents::REGISTRATION_SUCCEEDED => ['sendRegistrationEmail']
        ];
    }

    public function sendRegistrationEmail(GenericEvent $event): void
    {
        $userEntity = $event->getSubject();
        if (UsersConstant::ACTIVATED_PENDING_REG === $userEntity->getActivated()) {
            return;
        }
        $notificationErrors = $this->mailHelper->createAndSendUserMail($userEntity);
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
