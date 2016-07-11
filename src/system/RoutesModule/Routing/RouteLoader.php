<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\RoutesModule\Routing;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\ThemeModule\AbstractTheme;

/**
 * Custom loader following http://symfony.com/doc/current/cookbook/routing/custom_route_loader.html
 */
class RouteLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Zikula\Common\Translator\Translator
     */
    private $translator;

    /**
     * @var \ZikulaKernel
     */
    private $zikulaKernel;

    /**
     * RouteLoader constructor.
     *
     * @param EntityManagerInterface $em           Doctrine entity manager.
     * @param ContainerInterface     $container    Service container.
     * @param \ZikulaKernel          $zikulaKernel Zikula kernel.
     */
    public function __construct(EntityManagerInterface $em, ContainerInterface $container, \ZikulaKernel $zikulaKernel)
    {
        $this->em = $em;
        $this->container = $container;
        $this->zikulaKernel = $zikulaKernel;
        $this->translator = $container->get('translator');
    }

    /**
     * Finds all routes of all Zikula themes and modules.
     *
     * @return RouteCollection[]
     */
    private function findAll()
    {
        $modules = $this->zikulaKernel->getModules();
        $themes = $this->zikulaKernel->getThemes();
        $bundles = array_merge($modules, $themes);

        $topRouteCollection = new RouteCollection();
        $middleRouteCollection = new RouteCollection();
        $bottomRouteCollection = new RouteCollection();
        foreach ($bundles as $bundle) {
            list ($currentMiddleRouteCollection, $currentTopRouteCollection, $currentBottomRouteCollection) = $this->find($bundle);
            $middleRouteCollection->addCollection($currentMiddleRouteCollection);
            $topRouteCollection->addCollection($currentTopRouteCollection);
            $bottomRouteCollection->addCollection($currentBottomRouteCollection);
        }

        return [$middleRouteCollection, $topRouteCollection, $bottomRouteCollection];
    }

    /**
     * Load routes of the specified module from the module's configuration file.
     *
     * @param AbstractBundle $bundle
     *
     * @return RouteCollection[]
     */
    private function find(AbstractBundle $bundle)
    {
        if (!\ServiceUtil::hasContainer()) {
            \ServiceUtil::setContainer($this->container);
        }
        try {
            $path = $this->zikulaKernel->locateResource($bundle->getRoutingConfig());
        } catch (\InvalidArgumentException $e) {
            // Routing file does not exist (e.g. because the bundle could not be located).
            return [new RouteCollection(), new RouteCollection(), new RouteCollection()];
        }
        $name = $bundle->getName();

        $topRouteCollection = new RouteCollection();
        $middleRouteCollection = new RouteCollection();
        $bottomRouteCollection = new RouteCollection();

        /**
         * These are all routes of the module, as loaded by Symfony.
         * @var RouteCollection $routeCollection
         */
        $routeCollection = $this->import($path);

        // Add all resources from the imported route collection to the middleRouteCollection.
        // The actual collection (top, middle, bottom) to add the resources too does not matter,
        // they just must be added to one of them, so that they don't get lost.
        foreach ($routeCollection->getResources() as $resource) {
            $middleRouteCollection->addResource($resource);
        }
        // It would be great to auto-reload routes here if the module version changes or a module is uninstalled.
        // This is not yet possible, see
        // - https://github.com/symfony/symfony/issues/7176
        // - https://github.com/symfony/symfony/pull/15738
        // - https://github.com/symfony/symfony/pull/15692
        // $routeCollection->addResource(new ZikulaResource())

        /** @var Route $route */
        foreach ($routeCollection as $oldRouteName => $route) {
//          set break here with $oldRouteName == 'zikula_routesmodule_route_renew'
            $this->fixRequirements($route);
            $this->prependBundlePrefix($route, $bundle);
            list($type, $func) = $this->setZikulaDefaults($route, $bundle, $name);
            $routeName = $this->getRouteName($oldRouteName, $name, $type, $func);

            if ($route->hasOption('zkPosition')) {
                switch ($route->getOption('zkPosition')) {
                    case 'top':
                        $topRouteCollection->add($routeName, $route);
                        break;
                    case 'bottom':
                        $bottomRouteCollection->add($routeName, $route);
                        break;
                    default:
                        throw new \RuntimeException('Unknown route position. Got "' . $route->getOption('zkPosition') . '", expected "top" or "bottom"');
                }
            } else {
                $middleRouteCollection->add($routeName, $route);
            }
        }

        return [$middleRouteCollection, $topRouteCollection, $bottomRouteCollection];
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "zikularoutesmodule" loader twice');
        }
        unset($type);

        $routeCollection = new RouteCollection();

        list ($newRouteCollection, $topRouteCollection, $bottomRouteCollection) = $this->findAll();

        $routeCollection->addCollection($topRouteCollection);

//        try {
//            $routes = $this->em->getRepository('ZikulaRoutesModule:RouteEntity')->findBy([], ['group' => 'ASC', 'sort' => 'ASC']);
//        } catch (DBALException $e) {
//            // It seems like the module is not yet installed. Fail silently.
//            return $routeCollection;
//        }
//
//        if (!empty($routes)) {
//            $helper = new RoutingUtil();
//            /**
//             * @var \Zikula\RoutesModule\Entity\RouteEntity $dbRoute
//             */
//            foreach ($routes as $dbRoute) {
//                // Add modname, type and func to the route's default values.
//                $defaults = $dbRoute->getDefaults();
//                $defaults['_zkModule'] = $dbRoute->getBundle();
//                list (, $type) = $helper->sanitizeController($dbRoute->getController());
//                list (, $func) = $helper->sanitizeAction($dbRoute->getAction());
//                $defaults['_zkType'] = $type;
//                $defaults['_zkFunc'] = $func;
//                // @todo @cmfcmf when reimplementing loading routews from DB, see #2593 (i.e. ucfirst problems)
//                $defaults['_controller'] = $dbRoute->getBundle() . ":" . ucfirst($type) . ":" . ucfirst($func);
//
//                // We have to prepend the bundle prefix if
//                // - routes are _not_ currently extracted via the command line and
//                // - the route has i18n set to false.
//                // This is because when extracting the routes, a bundle author only wants to translate the bare route
//                // patterns, without a redundant and potentially customized bundle prefix in front of them.
//                // If i18n is set to true, Zikula's customized pattern generation strategy will take care of it.
//                // See Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy
//                $options = $dbRoute->getOptions();
//                $prependBundle = !isset($GLOBALS['translation_extract_routes']) && isset($options['i18n']) && !$options['i18n'];
//                if ($prependBundle) {
//                    $path = $dbRoute->getPathWithBundlePrefix($this->container);
//                } else {
//                    $path = $dbRoute->getPath();
//                }
//
//                $requirements = $dbRoute->getRequirements();
//                // @todo Remove when Symfony 3.0 is used.
//                if (isset($requirements['_method'])) {
//                    unset($requirements['_method']);
//                }
//                if (isset($requirements['_scheme'])) {
//                    unset($requirements['_scheme']);
//                }
//
//                $route = new Route(
//                    $path,
//                    $defaults,
//                    $requirements,
//                    $options,
//                    $dbRoute->getHost(),
//                    $dbRoute->getSchemes(),
//                    $dbRoute->getMethods(),
//                    $dbRoute->getCondition()
//                );
//
//                $routeCollection->add($dbRoute->getName(), $route);
//            }
//        }
        $routeCollection->addCollection($newRouteCollection);
        $routeCollection->addCollection($bottomRouteCollection);

        $this->loaded = true;

        return $routeCollection;
    }

    /**
     * Sets some Zikula-specific defaults for the routes.
     *
     * @param Route $route The route instance.
     * @param AbstractBundle $bundle The bundle.
     * @param string $bundleName The bundle's name.
     * @return array The legacy $type and $func parameters.
     */
    private function setZikulaDefaults(Route $route, AbstractBundle $bundle, $bundleName)
    {
        $defaults = $route->getDefaults();

        $defaults['_zkBundle'] = $bundleName;
        if ($bundle instanceof AbstractModule) {
            $defaults['_zkModule'] = $bundleName;
        } else if ($bundle instanceof AbstractTheme) {
            $defaults['_zkTheme'] = $bundleName;
        }

        $controller = $this->sanitizeController($bundleName, $defaults['_controller']);
        $controller = explode(':', $controller);
        $defaults['_zkType'] = $type = lcfirst($controller[1]);
        $defaults['_zkFunc'] = $func = $controller[2];
        $defaults['_controller'] = $bundleName . ":" . $controller[1] . ":" . $func;

        $route->setDefaults($defaults);

        return [$type, $func];
    }

    /**
     * Removes some deprecated requirements which cause depreciation notices.
     *
     * @param Route $route
     *
     * @todo Remove when Symfony 3.0 is used.
     */
    private function fixRequirements(Route $route)
    {
        $requirements = $route->getRequirements();
        if (isset($requirements['_method'])) {
            unset($requirements['_method']);
        }
        if (isset($requirements['_scheme'])) {
            unset($requirements['_scheme']);
        }
        $route->setRequirements($requirements);
    }

    /**
     * Prepends the bundle prefix to the route.
     *
     * @param Route $route
     * @param AbstractBundle $bundle
     *
     * We have to prepend the bundle prefix if
     * - routes are _not_ currently extracted via the command line and
     * - the route has i18n set to false.
     * This is because when extracting the routes, a bundle author only wants to translate the bare route
     * patterns, without a redundant and potentially customized bundle prefix in front of them.
     * If i18n is set to true, Zikula's customized pattern generation strategy will take care of it.
     * See Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy
     */
    private function prependBundlePrefix(Route $route, AbstractBundle $bundle)
    {
        $prefix = '';
        $options = $route->getOptions();
        $prependBundle = !isset($GLOBALS['translation_extract_routes']) && isset($options['i18n']) && !$options['i18n'];
        if ($prependBundle && (!isset($options['zkNoBundlePrefix']) || !$options['zkNoBundlePrefix'])) {
            // get url from MetaData first. May be empty.
            $untranslatedPrefix = $bundle->getMetaData()->getUrl(false);
            if (empty($untranslatedPrefix)) {
                try {
                    // MetaData will be empty for extensions not Spec-2.0. Try to get from modinfo.
                    // this calls the DB which is not available during install.
                    $modinfo = \ModUtil::getInfoFromName($bundle->getName());
                    $prefix = $modinfo['url'];
                } catch (\Exception $e) {
                }
            } else {
                $locale = $this->container->getParameter('locale');
                if ($this->translator->getCatalogue($locale)->has($untranslatedPrefix, strtolower($bundle->getName()))) {
                    $prefix = $this->translator->trans(/** @Ignore */$untranslatedPrefix, [], strtolower($bundle->getName()), $locale);
                } else {
                    $prefix = $untranslatedPrefix;
                }
            }

            $path = "/" . $prefix . $route->getPath();
            $route->setPath($path);
        }
    }

    /**
     * Converts the controller identifier into a unified form.
     *
     * @param string $bundleName The name of the bundle
     * @param string $controllerString The given controller identifier.
     * @return string The controller identifier in a Bundle:Type:func form.
     */
    private function sanitizeController($bundleName, $controllerString)
    {
        if (strpos($controllerString, '::') === false) {
            return $controllerString;
        }

        $action = substr($controllerString, strpos($controllerString, '::') + 2);
        $func = lcfirst(substr($action, 0, -6));

        $a = strrpos($controllerString, '\\') + 1;
        $b = strrpos($controllerString, '::');
        $controller = substr($controllerString, $a, $b - $a);
        $type = substr($controller, 0, -10);

        return $bundleName . ':' . $type . ':' . $func;
    }

    /**
     * Generates the route's new name.
     *
     * @param string $oldRouteName The old route name.
     * @param string $bundleName   The bundle name.
     * @param string $type         The legacy type.
     * @param string $func         The legacy func.
     * @return string The route's new name.
     */
    private function getRouteName($oldRouteName, $bundleName, $type, $func)
    {
        $suffix = '';
        $lastPart = substr($oldRouteName, strrpos($oldRouteName, '_'));
        if (is_numeric($lastPart)) {
            // If the last part of the old route name is numeric, also append it to the new route name.
            // This allows multiple routes for the same action.
            $suffix = '_' . $lastPart;
        }
        return strtolower($bundleName . '_' . $type . '_' . $func) . $suffix;
    }

    public function supports($resource, $type = null)
    {
        return 'zikularoutesmodule' === $type;
    }
}
