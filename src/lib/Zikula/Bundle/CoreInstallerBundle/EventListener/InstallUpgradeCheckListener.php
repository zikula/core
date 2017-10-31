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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Util\VersionUtil;
use Zikula\Core\Event\GenericEvent;

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

    public function onCoreInit(GenericEvent $event)
    {
        /** @var Request $request */
        $request = $this->container->get('request');
        // create several booleans to test condition of request regarding install/upgrade
        $installed = $this->container->getParameter('installed');
        $requiresUpgrade = false;
        if ($installed) {
            VersionUtil::defineCurrentInstalledCoreVersion($this->container);
            $currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
            $requiresUpgrade = $installed && version_compare($currentVersion, ZikulaKernel::VERSION, '<');
        }

        $routeInfo = $this->container->get('router')->match($request->getPathInfo());
        $containsInstall = 'install' == $routeInfo['_route'];
        $containsUpgrade = 'upgrade' == $routeInfo['_route'];
        $containsLogin = 'Zikula\\UsersModule\\Controller\\AccessController::loginAction' == $routeInfo['_controller'] || 'Zikula\\UsersModule\\Controller\\AccessController::upgradeAdminLoginAction' == $routeInfo['_controller']; // @todo @deprecated at Core-2.0 remove later half
        $containsDoc = 'doc' == $routeInfo['_route'];
        $containsWdt =  '_wdt' == $routeInfo['_route'];
        $containsProfiler = false !== strpos($routeInfo['_route'], '_profiler');
        $containsRouter = 'fos_js_routing_js' == $routeInfo['_route'];
        $doNotRedirect = $containsProfiler || $containsWdt || $containsRouter || $request->isXmlHttpRequest();

        // check if Zikula Core is not installed
        if (!$installed && !$containsDoc && !$containsInstall && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('install');
            $this->container->get('zikula_routes_module.multilingual_routing_helper')->reloadMultilingualRoutingSettings();
            $response = new RedirectResponse($url);
            $response->send();
            \System::shutDown();
        }
        // check if Zikula Core requires upgrade
        if ($requiresUpgrade && !$containsLogin && !$containsDoc && !$containsUpgrade && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('upgrade');
            $this->container->get('zikula_routes_module.multilingual_routing_helper')->reloadMultilingualRoutingSettings();
            $response = new RedirectResponse($url);
            $response->send();
            \System::shutDown();
        }
    }

    public static function getSubscribedEvents()
    {
        // @todo can this be done on kernel.request instead?
        return [
            'core.init' => [
                ['onCoreInit']
            ],
        ];
    }
}
