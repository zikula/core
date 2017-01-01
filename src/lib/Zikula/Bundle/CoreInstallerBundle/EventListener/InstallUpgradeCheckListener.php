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
            $currentVersion = $this->container->getParameter(\ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
            $requiresUpgrade = $installed && version_compare($currentVersion, \ZikulaKernel::VERSION, '<');
        }

        $routeInfo = $this->container->get('router')->match($request->getPathInfo());
        $containsInstall = $routeInfo['_route'] == 'install';
        $containsUpgrade = $routeInfo['_route'] == 'upgrade';
        $containsLogin = $routeInfo['_controller'] == 'Zikula\\UsersModule\\Controller\\AccessController::loginAction' || $routeInfo['_controller'] == 'Zikula\\UsersModule\\Controller\\AccessController::upgradeAdminLoginAction'; // @todo @deprecated at Core-2.0 remove later half
        $containsDoc = $routeInfo['_route'] == 'doc';
        $containsWdt =  $routeInfo['_route'] == '_wdt';
        $containsProfiler = strpos($routeInfo['_route'], '_profiler') !== false;
        $containsRouter = $routeInfo['_route'] == 'fos_js_routing_js';
        $doNotRedirect = $containsProfiler || $containsWdt || $containsRouter || $request->isXmlHttpRequest();

        // check if Zikula Core is not installed
        if (!$installed && !$containsDoc && !$containsInstall && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('install');
            $this->loadLocales();
            $event->setResponse(new RedirectResponse($url));
        }
        // check if Zikula Core requires upgrade
        if ($requiresUpgrade && !$containsLogin && !$containsDoc && !$containsUpgrade && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('upgrade');
            $this->loadLocales();
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

    /**
     * Load locales from filesystem and setup jms_I18n_routing params
     */
    private function loadLocales()
    {
        // write locale choice to `config/dynamic/generated.yml`
        $configDumper = $this->container->get('zikula.dynamic_config_dumper');
        $config = $configDumper->getConfiguration('jms_i18n_routing');
        $config['locales'] = \ZLanguage::getInstalledLanguages();
        if (!in_array($this->container->getParameter('locale'), $config['locales'])) {
            $this->container->setParameter('locale', $config['locales'][0]);
            $config['default_locale'] = $config['locales'][0];
        }
        $configDumper->setConfiguration('jms_i18n_routing', $config);
        $this->container->get('zikula.cache_clearer')->clear('symfony');
    }
}
