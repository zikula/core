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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
        FormFactory $formFactory
    ) {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
    }

    public function onKernelRequestSiteOff(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        // Get variables
        $module = strtolower($request->query->get('module'));
        $type = strtolower($request->query->get('type'));
        $func = strtolower($request->query->get('func'));
        $siteOff = (bool)$this->variableApi->get(VariableApi::CONFIG, 'siteoff');
        $hasAdminPerms = $this->permissionApi->hasPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN);
        $urlParams = ($module == 'users' && $type == 'user' && $func == 'siteofflogin'); // params are lowercase
        $versionCheck = (\Zikula_Core::VERSION_NUM != $this->variableApi->get(VariableApi::CONFIG, 'Version_Num'));

        // Check for site closed
        if (($siteOff && !$hasAdminPerms && !$urlParams) || $versionCheck) {
            $hasOnlyOverviewAccess = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW);
            if ($hasOnlyOverviewAccess && $this->currentUserApi->isLoggedIn()) {
                $request->getSession()->invalidate(); // logout
            }

            $form = $this->formFactory->create('Zikula\ZAuthModule\Form\Type\UnameLoginType');
            $response = new Response();
            $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
            $response->setStatusCode(503);
            $content = $this->templating->render('@CoreBundle/System/sitoff.html.twig', [
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
                ['onKernelRequestSiteOff', 200] // priority set high to catch request before other subscribers
            ]
        ];
    }
}
