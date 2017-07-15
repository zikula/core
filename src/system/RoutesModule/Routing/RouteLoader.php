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

use Doctrine\DBAL\DBALException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\RoutesModule\Entity\Factory\EntityFactory;
use Zikula\RoutesModule\Helper\PathBuilderHelper;
use Zikula\RoutesModule\Helper\SanitizeHelper;
use Zikula\ThemeModule\AbstractTheme;

/**
 * Custom route loader.
 *
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
class RouteLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var PathBuilderHelper
     */
    private $pathBuilderHelper;

    /**
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * @var string
     */
    private $locale;

    /**
     * RouteLoader constructor.
     *
     * @param ZikulaHttpKernelInterface $kernel            Zikula kernel
     * @param TranslatorInterface       $translator        Translator
     * @param EntityFactory             $entityFactory     Entity factory
     * @param PathBuilderHelper         $pathBuilderHelper Path builder helper
     * @param SanitizeHelper            $sanitizeHelper    Sanitize helper
     * @param string                    $locale
     */
    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        EntityFactory $entityFactory,
        PathBuilderHelper $pathBuilderHelper,
        SanitizeHelper $sanitizeHelper,
        $locale)
    {
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->entityFactory = $entityFactory;
        $this->pathBuilderHelper = $pathBuilderHelper;
        $this->sanitizeHelper = $sanitizeHelper;
        $this->locale = $locale;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "zikularoutesmodule" loader twice');
        }
        unset($type);

        $routeCollection = new RouteCollection();

        list ($topRoutes, $middleRoutes, $bottomRoutes) = $this->findAll();

        $routeCollection->addCollection($topRoutes);

        try {
            $customRoutes = $this->entityFactory->getRepository('route')->findBy([], ['sort' => 'ASC']);
        } catch (DBALException $e) {
            $routeCollection->addCollection($middleRoutes);
            $routeCollection->addCollection($bottomRoutes);

            // It seems like the module is not yet installed. Fail silently.
            return $routeCollection;
        }

        if (!empty($customRoutes)) {
            $this->addCustomRoutes($routeCollection, $customRoutes);
        }

        $routeCollection->addCollection($middleRoutes);
        $routeCollection->addCollection($bottomRoutes);

        $this->loaded = true;

        return $routeCollection;
    }

    /**
     * Finds all routes of all Zikula themes and modules.
     *
     * @return RouteCollection[]
     */
    private function findAll()
    {
        $modules = $this->kernel->getModules();
        $themes = $this->kernel->getThemes();
        $bundles = array_merge($modules, $themes);

        $topRoutes = new RouteCollection();
        $middleRoutes = new RouteCollection();
        $bottomRoutes = new RouteCollection();
        foreach ($bundles as $bundle) {
            list ($currentMiddleRoutes, $currentTopRoutes, $currentBottomRoutes) = $this->find($bundle);
            $middleRoutes->addCollection($currentMiddleRoutes);
            $topRoutes->addCollection($currentTopRoutes);
            $bottomRoutes->addCollection($currentBottomRoutes);
        }

        return [$topRoutes, $middleRoutes, $bottomRoutes];
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
        try {
            $path = $this->kernel->locateResource($bundle->getRoutingConfig());
        } catch (\InvalidArgumentException $e) {
            // Routing file does not exist (e.g. because the bundle could not be located).
            return [new RouteCollection(), new RouteCollection(), new RouteCollection()];
        }
        $name = $bundle->getName();

        $topRoutes = new RouteCollection();
        $middleRoutes = new RouteCollection();
        $bottomRoutes = new RouteCollection();

        /**
         * These are all routes of the module, as loaded by Symfony.
         * @var RouteCollection $routeCollection
         */
        $routeCollection = $this->import($path);

        // Add all resources from the imported route collection to the middleRouteCollection.
        // The actual collection (top, middle, bottom) to add the resources too does not matter,
        // they just must be added to one of them, so that they don't get lost.
        foreach ($routeCollection->getResources() as $resource) {
            $middleRoutes->addResource($resource);
        }

        /** @var Route $route */
        foreach ($routeCollection as $oldRouteName => $route) {
            // set break here with $oldRouteName == 'zikula_routesmodule_route_renew'
            $this->fixRequirements($route);
            $this->prependBundlePrefix($route, $bundle);
            list($type, $func) = $this->setZikulaDefaults($route, $bundle, $name);
            $routeName = $this->getRouteName($oldRouteName, $name, $type, $func);

            if ($route->hasOption('zkPosition')) {
                switch ($route->getOption('zkPosition')) {
                    case 'top':
                        $topRoutes->add($routeName, $route);
                        break;
                    case 'bottom':
                        $bottomRoutes->add($routeName, $route);
                        break;
                    default:
                        $middleRoutes->add($routeName, $route);
                }
            } else {
                $middleRoutes->add($routeName, $route);
            }
        }

        return [$topRoutes, $middleRoutes, $bottomRoutes];
    }

    /**
     * Adds custom routes from database to the given route collection.
     *
     * @param RouteCollection $routeCollection The route collection
     * @param array           $customRoutes    List of custom routes to add
     */
    private function addCustomRoutes(RouteCollection $routeCollection, $customRoutes = [])
    {
        /**
         * @var \Zikula\RoutesModule\Entity\RouteEntity $dbRoute
         */
        foreach ($customRoutes as $dbRoute) {
            // Add modname, type and func to the route's default values.
            $defaults = $dbRoute->getDefaults();
            $defaults['_zkModule'] = $dbRoute->getBundle();
            list (, $type) = $this->sanitizeHelper->sanitizeController($dbRoute->getController());
            list (, $func) = $this->sanitizeHelper->sanitizeAction($dbRoute->getAction());
            $defaults['_zkType'] = $type;
            $defaults['_zkFunc'] = $func;
            $defaults['_controller'] = $dbRoute->getBundle() . ":" . ucfirst($type) . ":" . ucfirst($func);

            // We have to prepend the bundle prefix (see detailed description in docblock of prependBundlePrefix() method).
            $options = $dbRoute->getOptions();
            $prependBundle = !isset($GLOBALS['translation_extract_routes']) && isset($options['i18n']) && !$options['i18n'];
            if ($prependBundle) {
                $path = $this->pathBuilderHelper->getPathWithBundlePrefix($dbRoute);
            } else {
                $path = $dbRoute->getPath();
            }

            $schemes = explode('###', $dbRoute->getSchemes());
            $methods = explode('###', $dbRoute->getMethods());

            $route = new Route(
                $path,
                $defaults,
                $dbRoute->getRequirements(),
                $options,
                $dbRoute->getHost(),
                $schemes,
                $methods,
                $dbRoute->getCondition()
            );

            $this->fixRequirements($route);

            $routeName = 'custom_' . str_replace('/', '_', $path);
            $routeCollection->add($routeName, $route);
        }
    }

    /**
     * Sets some Zikula-specific defaults for the routes.
     *
     * @param Route          $route The route instance
     * @param AbstractBundle $bundle The bundle
     * @param string         $bundleName The bundle's name
     *
     * @return array The legacy $type and $func parameters
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
        $defaults['_zkFunc'] = $func = lcfirst($controller[2]);
        $defaults['_controller'] = $bundleName . ':' . $controller[1] . ':' . $func;

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
     * @param Route          $route
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
        if (!$prependBundle || (isset($options['zkNoBundlePrefix']) && $options['zkNoBundlePrefix'])) {
            return;
        }

        // get url from MetaData first. May be empty.
        $untranslatedPrefix = $bundle->getMetaData()->getUrl(false);
        if (empty($untranslatedPrefix)) {
            // @todo remove in 2.0
            try {
                // MetaData will be empty for extensions not Spec-2.0. Try to get from modinfo.
                // this calls the DB which is not available during install.
                $modinfo = \ModUtil::getInfoFromName($bundle->getName());
                $prefix = $modinfo['url'];
            } catch (\Exception $e) {
            }
        } else {
            if ($this->translator->getCatalogue($this->locale)->has($untranslatedPrefix, strtolower($bundle->getName()))) {
                $prefix = $this->translator->trans(/** @Ignore */$untranslatedPrefix, [], strtolower($bundle->getName()), $this->locale);
            } else {
                $prefix = $untranslatedPrefix;
            }
        }

        $path = '/' . $prefix . $route->getPath();
        $route->setPath($path);
    }

    /**
     * Converts the controller identifier into a unified form.
     *
     * @param string $bundleName The name of the bundle
     * @param string $controllerString The given controller identifier
     *
     * @return string The controller identifier in a Bundle:Type:func form
     */
    private function sanitizeController($bundleName, $controllerString)
    {
        if (0 === preg_match('#^(.*?\\\\Controller\\\\(.+)Controller)::(.+)Action$#', $controllerString, $match)) {
            return $controllerString;
        }

        // Bundle:controller:action
        return $bundleName . ':' . $match[2] . ':' . $match[3];
    }

    /**
     * Generates the route's new name.
     *
     * @param string $oldRouteName The old route name
     * @param string $bundleName   The bundle name
     * @param string $type         The legacy type
     * @param string $func         The legacy func
     *
     * @return string The route's new name
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

    /**
     * Checks whether this route loader supports a given route type or not.
     *
     * @return boolean
     */
    public function supports($resource, $type = null)
    {
        return 'zikularoutesmodule' === $type;
    }
}
