<?php

namespace Zikula\RoutesModule\Routing;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\Core\AbstractTheme;

/**
 * Class RouteLoader.
 *
 * Custom loader following http://symfony.com/doc/current/cookbook/routing/custom_route_loader.html
 */
class RouteLoader extends Loader
{
    private $loaded = false;

    private $em;

    private $container;

    /**
     * @var \ZikulaKernel
     */
    private $zikulaKernel;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, \ZikulaKernel $zikulaKernel)
    {
        $this->em = $em;
        $this->container = $container;
        $this->zikulaKernel = $zikulaKernel;
    }

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
     * Finds all routes of all Zikula themes and modules.
     *
     * @return RouteCollection[]
     */
    private function findAll()
    {
        $modules = $this->zikulaKernel->getModules();
        $themes = $this->zikulaKernel->getThemes();

        $routeCollection = new RouteCollection();
        $topRouteCollection = new RouteCollection();
        $bottomRouteCollection = new RouteCollection();
        foreach ($modules as $module) {
            list ($currentRouteCollection, $currentTopRouteCollection, $currentBottomRouteCollection) = $this->find($module);
            $routeCollection->addCollection($currentRouteCollection);
            $topRouteCollection->addCollection($currentTopRouteCollection);
            $bottomRouteCollection->addCollection($currentBottomRouteCollection);
        }
        foreach ($themes as $theme) {
            list ($currentRouteCollection, $currentTopRouteCollection, $currentBottomRouteCollection) = $this->find($theme);
            $routeCollection->addCollection($currentRouteCollection);
            $topRouteCollection->addCollection($currentTopRouteCollection);
            $bottomRouteCollection->addCollection($currentBottomRouteCollection);
        }

        return [$routeCollection, $topRouteCollection, $bottomRouteCollection];
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
            // Routing file does not exist (e.g. because the bundle could not be located)
            return [new RouteCollection(), new RouteCollection(), new RouteCollection()];
        }
        $name = $bundle->getName();

        $routeCollection = new RouteCollection();
        $topRouteCollection = new RouteCollection();
        $bottomRouteCollection = new RouteCollection();

        /** @var RouteCollection $currentRouteCollection */
        $currentRouteCollection = $this->import($path);
        foreach ($currentRouteCollection->getResources() as $resource) {
            $routeCollection->addResource($resource);
        }

        /** @var Route $route */
        foreach ($currentRouteCollection as $oldRouteName => $route) {
            $defaults = $route->getDefaults();

            $defaults['_zkBundle'] = $name;
            if ($bundle instanceof AbstractModule) {
                $defaults['_zkModule'] = $name;
            } else if ($bundle instanceof AbstractTheme) {
                $defaults['_zkTheme'] = $name;
            }

            $controller = $this->sanitizeController($name, $defaults['_controller']);
            $controller = explode(':', $controller);
            $defaults['_zkType'] = $type = lcfirst($controller[1]);
            $defaults['_zkFunc'] = $func = $controller[2];
            $defaults['_controller'] = $name . ":" . ucfirst($type) . ":" . ucfirst($func);

            $route->setDefaults($defaults);

            // We have to prepend the bundle prefix if
            // - routes are _not_ currently extracted via the command line and
            // - the route has i18n set to false.
            // This is because when extracting the routes, a bundle author only wants to translate the bare route
            // patterns, without a redundant and potentially customized bundle prefix in front of them.
            // If i18n is set to true, Zikula's customized pattern generation strategy will take care of it.
            // See Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy
            $options = $route->getOptions();
            $prependBundle = !isset($GLOBALS['translation_extract_routes']) && isset($options['i18n']) && !$options['i18n'];
            if ($prependBundle && (!isset($options['zkNoBundlePrefix']) || !$options['zkNoBundlePrefix'])) {
                $modinfo = \ModUtil::getInfoFromName($name);

                $path = "/" . $modinfo["url"] . $route->getPath();
                $route->setPath($path);
            }

            // @todo Remove when Symfony 3.0 is used.
            $requirements = $route->getRequirements();
            if (isset($requirements['_method'])) {
                unset($requirements['_method']);
            }
            if (isset($requirements['_scheme'])) {
                unset($requirements['_scheme']);
            }
            $route->setRequirements($requirements);

            $suffix = '';
            $lastPart = substr($oldRouteName, strrpos($oldRouteName, '_'));
            if (is_numeric($lastPart)) {
                $suffix = '_' . $lastPart;
            }
            $routeName = strtolower($name . '_' . $type . '_' . $func) . $suffix;
            if (isset($options['zkPosition']) && in_array($options['zkPosition'], ['top', 'bottom'])) {
                if ($options['zkPosition'] == 'top') {
                    $topRouteCollection->add($routeName, $route);
                } else {
                    $bottomRouteCollection->add($routeName, $route);
                }
            } else {
                $routeCollection->add($routeName, $route);
            }
        }

        return [$routeCollection, $topRouteCollection, $bottomRouteCollection];
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
        // We would need a DatabaseResource or similar, which does not exist in Symfony (yet).
        // See https://github.com/symfony/symfony/issues/7176
        // $routeCollection->addResource(new FileResource())

        return $routeCollection;
    }

    public function supports($resource, $type = null)
    {
        return 'zikularoutesmodule' === $type;
    }
}
