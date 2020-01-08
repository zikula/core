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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\AccessEvents;

class SiteOffVetoLoginListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $siteOff;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack
    ) {
        $this->siteOff = $variableApi->getSystemVar('siteoff');
        $this->permissionApi = $permissionApi;
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [
            AccessEvents::LOGIN_VETO => [
                ['vetoNonAdminsOnSiteOff']
            ]
        ];
    }

    /**
     * Veto a login by a non-admin when the site is disabled.
     */
    public function vetoNonAdminsOnSiteOff(GenericEvent $event): void
    {
        if (!$this->siteOff) {
            return;
        }
        $user = $event->getSubject();
        if (!$this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, $user->getUid())) {
            $event->stopPropagation();

            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                $session->remove('authenticationMethod');
            }
            $event->setArgument('flash', $this->translator->trans('Admin credentials required when site is disabled.'));
            $event->setArgument('returnUrl', $this->router->generate('home'));
        }
    }
}
