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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Event\UserPreLoginSuccessEvent;

class SiteOffVetoLoginListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly bool $maintenanceModeEnabled
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserPreLoginSuccessEvent::class => ['vetoNonAdminsOnSiteOff'],
        ];
    }

    /**
     * Veto a login by a non-admin when the site is disabled.
     */
    public function vetoNonAdminsOnSiteOff(UserPreLoginSuccessEvent $event): void
    {
        if (!$this->maintenanceModeEnabled) {
            return;
        }

        $user = $event->getUser();
        if (!$this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, $user->getUid())) {
            $event->stopPropagation();

            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                $session->remove('authenticationMethod');
            }
            $event->addFlash($this->translator->trans('Admin credentials required when site is disabled.'));
            $event->setRedirectUrl($this->router->generate('home'));
        }
    }
}
