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

use UserUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Constant as UsersConstant;

class UserEventListener implements EventSubscriberInterface
{
    private $session;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public static function getSubscribedEvents()
    {
        return array(
            'module.users.ui.logout.succeeded' => array('clearUsersNamespace'),
            KernelEvents::EXCEPTION => array('clearUsersNamespace'),
            'user.login.veto' => array('forcedPasswordChange'),
        );
    }

    public function __construct(\Zikula_Session $session, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
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
            $this->session->clearNamespace(UsersConstant::SESSION_VAR_NAMESPACE);
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
     * @return void
     *
     * @throws \RuntimeException Thrown if the user hasn't changed the account password
     */
    public function forcedPasswordChange(GenericEvent $event)
    {
        $userObj = $event->getSubject();

        $userMustChangePassword = UserUtil::getVar('_Users_mustChangePassword', $userObj['uid'], false);

        if ($userMustChangePassword && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
            $event->stopPropagation();
            $event->setData(array(
                'redirect_func' => array(
                    'modname' => UsersConstant::MODNAME,
                    'type'    => 'user',
                    'func'    => 'changePassword',
                    'args'    => array(
                        'login' => true,
                    ),
                    'session' => array(
                        'var'       => 'User_changePassword',
                        'namespace' => UsersConstant::SESSION_VAR_NAMESPACE,
                    )
                ),
            ));

            $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add('error', __("Your log-in request was not completed. You must change your web site account's password first."));
        }
    }
}
