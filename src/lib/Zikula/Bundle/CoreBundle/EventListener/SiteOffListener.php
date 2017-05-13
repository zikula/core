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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

class SiteOffListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var boolean
     */
    private $installed;

    /**
     * @var string
     */
    private $currentInstalledVersion;

    /**
     * SiteOffListener constructor.
     * @param VariableApiInterface $variableApi
     * @param PermissionApiInterface $permissionApi
     * @param CurrentUserApiInterface $currentUserApi
     * @param \Twig_Environment $twig
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param boolean $installed
     * @param string $currentInstalledVersion
     */
    public function __construct(
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        \Twig_Environment $twig,
        TranslatorInterface $translator,
        RouterInterface $router,
        $installed,
        $currentInstalledVersion
    ) {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->router = $router;
        $this->installed = $installed;
        $this->currentInstalledVersion = $currentInstalledVersion;
    }

    public function onKernelRequestSiteOff(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();
        $this->router->getContext()->setBaseUrl($request->getBaseUrl());
        $routeInfo = $this->router->match($request->getPathInfo());
        if ($routeInfo['_route'] == 'zikulausersmodule_access_login'
        || $routeInfo['_route'] == 'zikulathememodule_combinedasset_asset') {
            return;
        }
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        $siteOff = (bool)$this->variableApi->getSystemVar('siteoff');
        $hasAdminPerms = $this->permissionApi->hasPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN);
        $currentInstalledVersion = !empty($this->currentInstalledVersion)
            ? $this->currentInstalledVersion
            : $this->variableApi->getSystemVar('Version_Num');
        $versionsEqual = (ZikulaKernel::VERSION == $currentInstalledVersion);

        // Check for site closed
        if (($siteOff || !$versionsEqual) && !$hasAdminPerms) {
            $hasOnlyOverviewAccess = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW);
            if ($hasOnlyOverviewAccess && $request->hasSession() && $this->currentUserApi->isLoggedIn()) {
                $request->getSession()->invalidate(); // logout
            }
            $response = new PlainResponse();
            $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
            $response->setStatusCode(503);
            $content = $this->twig->render('CoreBundle:System:siteoff.html.twig', [
                'versionsEqual' => $versionsEqual,
            ]);
            $response->setContent($content);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * Veto a login by a non-admin when the site is disabled.
     * @param GenericEvent $event
     */
    public function vetoNonAdminsOnSiteOff(GenericEvent $event)
    {
        $siteOff = (bool)$this->variableApi->getSystemVar('siteoff');
        if (!$siteOff) {
            return;
        }
        $user = $event->getSubject();
        if (!$this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, $user->getUid())) {
            $event->stopPropagation();
            $event->setArgument('flash', $this->translator->__('Admin credentials required when site is disabled.'));
            $event->setArgument('returnUrl', $this->router->generate('home'));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestSiteOff', 110] // priority set high to catch request before other subscribers
            ],
            AccessEvents::LOGIN_VETO => [
                ['vetoNonAdminsOnSiteOff']
            ]
        ];
    }
}
