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

namespace Zikula\ZAuthBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\UsersBundle\Event\UserPreLoginSuccessEvent;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\ZAuthConstant;

class UserEventListener implements EventSubscriberInterface
{
    public function __construct(private readonly RequestStack $requestStack, private readonly RouterInterface $router)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            UserPreLoginSuccessEvent::class => ['forcedPasswordChange'],
        ];
    }

    /**
     * Vetoes (denies) a login attempt, and forces the user to change his password.
     * This handler is triggered by the 'UserPreLoginSuccessEvent'.  It vetoes (denies) a
     * login attempt if the users's account record is flagged to force the user to change
     * his password maintained by the Users bundle. If the user does not maintain a
     * password on his Users account (e.g., he registered with and logs in with a Google
     * Account or an OpenID, and never established a Users password), then this handler
     * will not trigger a change of password.
     *
     * @see \Zikula\ZAuthBundle\Controller\AccountController::changePasswordAction
     */
    public function forcedPasswordChange(UserPreLoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY) && $user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
            $event->stopPropagation();
            $event->setRedirectUrl($this->router->generate('zikulazauthbundle_account_changepassword'));

            $request = $this->requestStack->getCurrentRequest();
            if ($request->hasSession() && ($session = $request->getSession())) {
                $session->set('authenticationMethod', $event->getAuthenticationMethod());
                $session->set(UsersConstant::FORCE_PASSWORD_SESSION_UID_KEY, $user->getUid());
            }
            $event->addFlash("Your log-in request was not completed. You must change your web site account's password first.");
        }
    }
}
