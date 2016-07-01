<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Listener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class UserEventListener implements EventSubscriberInterface
{
    /**
     * @var \Zikula_Session
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    public static function getSubscribedEvents()
    {
        return array(
            AccessEvents::LOGIN_VETO => array('forcedPasswordChange'),
        );
    }

    public function __construct(\Zikula_Session $session, RouterInterface $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * Vetos (denies) a login attempt, and forces the user to change his password.
     * This handler is triggered by the 'user.login.veto' event.  It vetos (denies) a
     * login attempt if the users's account record is flagged to force the user to change
     * his password maintained by the Users module. If the user does not maintain a
     * password on his Users account (e.g., he registered with and logs in with a Google
     * Account or an OpenID, and never established a Users password), then this handler
     * will not trigger a change of password.
     *
     * @param GenericEvent $event The event that triggered this handler.
     *
     * @see \Zikula\ZAuthModule\Controller\AccountController::changePasswordAction
     */
    public function forcedPasswordChange(GenericEvent $event)
    {
        /** @var UserEntity $user */
        $user = $event->getSubject();
        if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY) && $user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
            $event->stopPropagation();
            $event->setArgument('returnUrl', $this->router->generate('zikulazauthmodule_account_changepassword'));
            $this->session->set('authenticationMethod', $event->getArgument('authenticationMethod'));
            $this->session->set(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY, $user->getUid());

            $this->session->getFlashBag()->add('error', __("Your log-in request was not completed. You must change your web site account's password first."));
        }
    }
}
