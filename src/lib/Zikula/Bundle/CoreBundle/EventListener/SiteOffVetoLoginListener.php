<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
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
     * @var SessionInterface
     */
    private $session;

    /**
     * SiteOffListener constructor.
     * @param bool $siteOff
     * @param PermissionApiInterface $permissionApi
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param SessionInterface $session
     */
    public function __construct(
        $siteOff,
        PermissionApiInterface $permissionApi,
        TranslatorInterface $translator,
        RouterInterface $router,
        SessionInterface $session
    ) {
        $this->siteOff = $siteOff;
        $this->permissionApi = $permissionApi;
        $this->translator = $translator;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * Veto a login by a non-admin when the site is disabled.
     * @param GenericEvent $event
     */
    public function vetoNonAdminsOnSiteOff(GenericEvent $event)
    {
        if (!$this->siteOff) {
            return;
        }
        $user = $event->getSubject();
        if (!$this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, $user->getUid())) {
            $event->stopPropagation();
            $this->session->remove('authenticationMethod');
            $event->setArgument('flash', $this->translator->__('Admin credentials required when site is disabled.'));
            $event->setArgument('returnUrl', $this->router->generate('home'));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            AccessEvents::LOGIN_VETO => [
                ['vetoNonAdminsOnSiteOff']
            ]
        ];
    }
}
