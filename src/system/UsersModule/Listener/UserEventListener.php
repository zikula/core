<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Listener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

class UserEventListener implements EventSubscriberInterface
{
    /**
     * @var \Zikula_Session
     */
    private $session;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    public static function getSubscribedEvents()
    {
        return array(
            AccessEvents::LOGOUT_SUCCESS => array('clearUsersNamespace'),
            KernelEvents::EXCEPTION => array('clearUsersNamespace'),
            AccessEvents::LOGIN_VETO => array('forcedPasswordChange'),
        );
    }

    public function __construct(\Zikula_Session $session, RequestStack $requestStack, RouterInterface $router)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     * Clears the session variable namespace used by the Users module.
     * Triggered by the 'user.logout.succeeded' and Kernel::EXCEPTION events.
     * This is to ensure no leakage of authentication information across sessions or between critical
     * errors. This prevents, for example, the login process from becoming confused about its state
     * if it detects session variables containing authentication information which might make it think
     * that a re-attempt is in progress.
     *
     * @param GenericEvent $event The event that triggered this handler.
     *
     * @return void
     */
    public function clearUsersNamespace($event, $eventName)
    {
        $doClear = false;
        if ($eventName == KernelEvents::EXCEPTION) {
            $request = $this->requestStack->getCurrentRequest();
            if (!is_null($request)) {
                $doClear = $request->attributes->has('_zkModule') && $request->attributes->get('_zkModule') == UsersConstant::MODNAME;
            }
        } else {
            // Logout
            $doClear = true;
        }

        if ($doClear) {
            $this->session->clear();
        }
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
     * @see \Zikula\UsersModule\Controller\AccountController::changePasswordAction
     */
    public function forcedPasswordChange(GenericEvent $event)
    {
        /** @var UserEntity $user */
        $user = $event->getSubject();
        if ($user->getAttributes()->containsKey('_Users_mustChangePassword') && $user->getAttributes()->get('_Users_mustChangePassword')
            && $user->getPass() != UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
            $event->stopPropagation();
            $event->setArgument('returnUrl', $this->router->generate('zikulausersmodule_account_changepassword'));
            $this->session->set('authenticationMethod', $event->getArgument('authenticationMethod'));
            $this->session->set(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY, $user->getUid());

            $this->session->getFlashBag()->add('error', __("Your log-in request was not completed. You must change your web site account's password first."));
        }
    }
}
