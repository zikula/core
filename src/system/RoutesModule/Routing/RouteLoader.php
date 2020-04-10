<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\RoutesModule\Routing;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\AbstractModule;
use Zikula\ExtensionsModule\AbstractTheme;
use Zikula\RoutesModule\Entity\Factory\EntityFactory;
use Zikula\RoutesModule\Entity\RouteEntity;
use Zikula\RoutesModule\Helper\ExtractTranslationHelper;
use Zikula\RoutesModule\Helper\PathBuilderHelper;
use Zikula\RoutesModule\Helper\SanitizeHelper;

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
     * @var ExtractTranslationHelper
     */
    private $extractTranslationHelper;

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

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        EntityFactory $entityFactory,
        ExtractTranslationHelper $extractTranslationHelper,
        PathBuilderHelper $pathBuilderHelper,
        SanitizeHelper $sanitizeHelper,
        string $locale)
    {
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->entityFactory = $entityFactory;
        $this->extractTranslationHelper = $extractTranslationHelper;
        $this->pathBuilderHelper = $pathBuilderHelper;
        $this->sanitizeHelper = $sanitizeHelper;
        $this->locale = $locale;
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->loaded) {
            throw new RuntimeException('Do not add the "zikularoutesmodule" loader twice');
        }

        $routeCollection = new RouteCollection();

        [$topRoutes, $middleRoutes, $bottomRoutes] = $this->findAll();

        $routeCollection->addCollection($topRoutes);

        try {
            $customRoutes = $this->entityFactory->getRepository('route')->findBy([], ['sort' => 'ASC']);
        } catch (Exception $exception) {
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
    private function findAll(): array
    {
        $modules = $this->kernel->getModules();
        $themes = $this->kernel->getThemes();
        $extensions = array_merge($modules, $themes);

        $topRoutes = new RouteCollection();
        $middleRoutes = new RouteCollection();
        $bottomRoutes = new RouteCollection();
        foreach ($extensions as $extension) {
            [$currentMiddleRoutes, $currentTopRoutes, $currentBottomRoutes] = $this->find($extension);
            $middleRoutes->addCollection($currentMiddleRoutes);
            $topRoutes->addCollection($currentTopRoutes);
            $bottomRoutes->addCollection($currentBottomRoutes);
        }

        return [$topRoutes, $middleRoutes, $bottomRoutes];
    }

    /**
     * Load routes of the specified module from the module's configuration file.
     *
     * @return RouteCollection[]
     */
    private function find(AbstractExtension $extension): array
    {
        try {
            $path = $this->kernel->locateResource($extension->getRoutingConfig());
        } catch (InvalidArgumentException $exception) {
            // Routing file does not exist (e.g. because the extension could not be located).
            return [new RouteCollection(), new RouteCollection(), new RouteCollection()];
        }
        $name = $extension->getName();

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
            $this->prependExtensionPrefix($route, $extension);
            [$type, $func] = $this->setZikulaDefaults($route, $extension, $name);
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
     */
    private function addCustomRoutes(RouteCollection $routeCollection, array $customRoutes = []): void
    {
        /** @var RouteEntity $dbRoute */
        foreach ($customRoutes as $dbRoute) {
            $extensionName = $dbRoute->getBundle();
            if (!$this->kernel->isBundle($extensionName)) {
                continue;
            }
            $extension = $this->kernel->getBundle($extensionName);

            // Add modname, type and func to the route's default values.
            $defaults = $dbRoute->getDefaults();
            $defaults['_zkModule'] = $extensionName;
            [, $type] = $this->sanitizeHelper->sanitizeController($dbRoute->getController());
            [, $func] = $this->sanitizeHelper->sanitizeAction($dbRoute->getAction());
            $defaults['_zkType'] = $type;
            $defaults['_zkFunc'] = $func;
            $defaults['_controller'] = $extension->getNamespace() . '\\Controller\\' . ucfirst($type) . 'Controller::' . lcfirst($func) . 'Action';

            // We have to prepend the extension prefix (see detailed description in docblock of prependExtensionPrefix() method).
            $options = $dbRoute->getOptions();
            $prependExtension = empty($this->extractTranslationHelper->getBundleName()) && isset($options['i18n']) && !$options['i18n'];
            if ($prependExtension) {
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

            $routeName = 'custom_' . str_replace('/', '_', $path);
            $routeCollection->add($routeName, $route);
        }
    }

    /**
     * Sets some Zikula-specific defaults for the routes.
     *
     * @return array The legacy $type and $func parameters
     */
    private function setZikulaDefaults(Route $route, AbstractExtension $extension, string $extensionName): array
    {
        $defaults = $route->getDefaults();

        $defaults['_zkBundle'] = $extensionName;
        if ($extension instanceof AbstractModule) {
            $defaults['_zkModule'] = $extensionName;
        } else if ($extension instanceof AbstractTheme) {
            $defaults['_zkTheme'] = $extensionName;
        }

        $controller = $this->sanitizeController($extensionName, $defaults['_controller']);
        $controller = explode(':', $controller);
        $defaults['_zkType'] = $type = lcfirst($controller[1]);
        $defaults['_zkFunc'] = $func = lcfirst($controller[2]);
        $defaults['_controller'] = $extension->getNamespace() . '\\Controller\\' . ucfirst($controller[1]) . 'Controller::' . $func . 'Action';

        $route->setDefaults($defaults);

        return [$type, $func];
    }

    /**
     * Prepends the extension prefix to the route.
     *
     * We have to prepend the extension prefix if
     * - routes are _not_ currently extracted via the command line and
     * - the route has i18n set to false.
     * This is because when extracting the routes, a extension author only wants to translate the bare route
     * patterns, without a redundant and potentially customized extension prefix in front of them.
     * If i18n is set to true, Zikula's customized pattern generation strategy will take care of it.
     * See Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy
     */
    private function prependExtensionPrefix(Route $route, AbstractExtension $extension)
    {
        $prefix = '';
        $options = $route->getOptions();
        $prependExtension = empty($this->extractTranslationHelper->getBundleName()) && isset($options['i18n']) && !$options['i18n'];
        if (!$prependExtension) {
            return;
        }
        if ((isset($options['zkNoBundlePrefix']) && $options['zkNoBundlePrefix'])) {
            return;
        }

        // get url from extension meta data first. May be empty.
        $untranslatedPrefix = $extension->getMetaData()->getUrl(false);
        if (!empty($untranslatedPrefix)) {
            if ($this->translator->getCatalogue($this->locale)->has($untranslatedPrefix, strtolower($extension->getName()))) {
                $prefix = $this->translator->trans(/** @Ignore */$untranslatedPrefix, [], strtolower($extension->getName()), $this->locale);
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
     * @return string The controller identifier in a Extension:Type:func form
     */
    private function sanitizeController(string $extensionName, string $controllerString): string
    {
        if (0 === preg_match('#^(.*?\\\\Controller\\\\(.+)Controller)::(.+)Action$#', $controllerString, $match)) {
            return $controllerString;
        }

        // Extension:controller:action
        return $extensionName . ':' . $match[2] . ':' . $match[3];
    }

    /**
     * Generates the route's new name.
     */
    private function getRouteName(string $oldRouteName, string $extensionName, string $type, string $func): string
    {
        $suffix = '';
        $lastHit = strrpos($oldRouteName, '_');
        if (false !== $lastHit) {
            $lastPart = substr($oldRouteName, $lastHit);
            if (is_numeric($lastPart)) {
                // If the last part of the old route name is numeric, also append it to the new route name.
                // This allows multiple routes for the same action.
                $suffix = '_' . $lastPart;
            }
        }

        return strtolower($extensionName . '_' . $type . '_' . $func) . $suffix;
    }

    /**
     * Checks whether this route loader supports a given route type or not.
     *
     * @return boolean
     */
    public function supports($resource, string $type = null)
    {
        return 'zikularoutesmodule' === $type;
    }
}
