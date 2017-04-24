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
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
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
     * @var FormFactory
     */
    private $formFactory;

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
     * @param FormFactory $formFactory
     * @param RouterInterface $router
     * @param $installed
     * @param $currentInstalledVersion
     */
    public function __construct(
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        \Twig_Environment $twig,
        FormFactory $formFactory,
        RouterInterface $router,
        $installed,
        $currentInstalledVersion
    ) {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
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
        if ($request->isMethod('POST') && $request->request->has('zikulazauthmodule_authentication_uname')) {
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

        // Get variables
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

            $route = version_compare($currentInstalledVersion, '1.4.3', '<') ? 'zikulausersmodule_access_upgradeadminlogin' : 'zikulausersmodule_access_login'; // @todo @deprecated remove at Core-2.0
            $form = $this->formFactory->create('Zikula\ZAuthModule\Form\Type\UnameLoginType', [], [
                'action' => $this->router->generate($route, ['authenticationMethod' => 'native_uname'])
            ]);
            $response = new PlainResponse();
            $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
            $response->setStatusCode(503);
            $content = $this->twig->render('CoreBundle:System:siteoff.html.twig', [
                'versionsEqual' => $versionsEqual,
                'form' => $form->createView()
            ]);
            $response->setContent($content);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestSiteOff', 110] // priority set high to catch request before other subscribers
            ]
        ];
    }
}
