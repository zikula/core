<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class InstallUpgradeCheckListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        /** @var Request $request */
        $request = $event->getRequest();
        // create several booleans to test condition of request regarding install/upgrade
        $installed = $this->container->getParameter('installed');
        $requiresUpgrade = false;
        if ($installed) {
            $currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
            $requiresUpgrade = $installed && version_compare($currentVersion, ZikulaKernel::VERSION, '<');
        }

        $routeInfo = $this->container->get('router')->match($request->getPathInfo());
        $containsInstall = $routeInfo['_route'] == 'install';
        $containsUpgrade = $routeInfo['_route'] == 'upgrade';
        $containsLogin = $routeInfo['_controller'] == 'Zikula\\UsersModule\\Controller\\AccessController::loginAction';
        $containsDoc = $routeInfo['_route'] == 'doc';
        $containsWdt = $routeInfo['_route'] == '_wdt';
        $containsProfiler = strpos($routeInfo['_route'], '_profiler') !== false;
        $containsRouter = $routeInfo['_route'] == 'fos_js_routing_js';
        $doNotRedirect = $containsProfiler || $containsWdt || $containsRouter || $request->isXmlHttpRequest();

        // check if Zikula Core is not installed
        if (!$installed && !$containsDoc && !$containsInstall && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('install');
            $this->container->get('zikula_routes_module.multilingual_routing_helper')->reloadMultilingualRoutingSettings();
            $event->setResponse(new RedirectResponse($url));
        }
        // check if Zikula Core requires upgrade
        if ($requiresUpgrade && !$containsLogin && !$containsDoc && !$containsUpgrade && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('upgrade');
            $this->container->get('zikula_routes_module.multilingual_routing_helper')->reloadMultilingualRoutingSettings();
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => [
                ['onKernelRequest', 200]
            ],
        ];
    }
}
