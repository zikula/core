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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;

class SiteOffListener implements EventSubscriberInterface
{
    private $variableApi;

    private $permissionApi;

    private $currentUserApi;

    private $templating;

    private $formFactory;

    private $router;

    private $installed;

    /**
     * OutputCompressionListener constructor.
     * @param VariableApi $variableApi
     * @param PermissionApi $permissionApi
     * @param CurrentUserApi $currentUserApi
     * @param EngineInterface $templating
     * @param FormFactory $formFactory
     */
    public function __construct(
        VariableApi $variableApi,
        PermissionApi $permissionApi,
        CurrentUserApi $currentUserApi,
        EngineInterface $templating,
        FormFactory $formFactory,
        RouterInterface $router,
        $installed
    ) {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->installed = $installed;
    }

    public function onKernelRequestSiteOff(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();
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
        $versionCheck = (\ZikulaKernel::VERSION != $this->variableApi->getSystemVar('Version_Num'));

        // Check for site closed
        if (($siteOff && !$hasAdminPerms) || $versionCheck) {
            $hasOnlyOverviewAccess = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW);
            if ($hasOnlyOverviewAccess && $request->hasSession() && $this->currentUserApi->isLoggedIn()) {
                $request->getSession()->invalidate(); // logout
            }

            $form = $this->formFactory->create('Zikula\ZAuthModule\Form\Type\UnameLoginType', [], [
                'action' => $this->router->generate('zikulausersmodule_access_login')
            ]);
            $response = new PlainResponse();
            $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
            $response->setStatusCode(503);
            $content = $this->templating->render('CoreBundle:System:siteoff.html.twig', [
                'versionsEqual' => !$versionCheck,
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
