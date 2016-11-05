<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Helper\MailHelper;
use Zikula\UsersModule\RegistrationEvents;

class RegistrationEventsListener implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var MailHelper
     */
    private $mailHelper;

    public static function getSubscribedEvents()
    {
        return [
            RegistrationEvents::REGISTRATION_SUCCEEDED => ['sendRegistrationEmail'],
        ];
    }

    /**
     * RegistrationEventsListener constructor.
     * @param SessionInterface $session
     * @param MailHelper $mailHelper
     */
    public function __construct(SessionInterface $session, MailHelper $mailHelper)
    {
        $this->session = $session;
        $this->mailHelper = $mailHelper;
    }

    /**
     * @param GenericEvent $event
     */
    public function sendRegistrationEmail(GenericEvent $event)
    {
        $userEntity = $event->getSubject();
        if ($userEntity->getActivated() == UsersConstant::ACTIVATED_PENDING_REG) {
            $notificationErrors = $this->mailHelper->createAndSendRegistrationMail($userEntity);
        } else {
            $notificationErrors = $this->mailHelper->createAndSendUserMail($userEntity);
        }
        if (!empty($notificationErrors)) {
            $this->session->getFlashBag()->add('error', implode('<br>', $notificationErrors));
        }
    }
}
