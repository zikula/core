<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Composer\Autoload\ClassLoader;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Zikula\AdminModule\ZikulaAdminModule;
use Zikula\BlocksModule\ZikulaBlocksModule;
use Zikula\CategoriesModule\ZikulaCategoriesModule;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\ExtensionsModule\ZikulaExtensionsModule;
use Zikula\GroupsModule\ZikulaGroupsModule;
use Zikula\MailerModule\ZikulaMailerModule;
use Zikula\MenuModule\ZikulaMenuModule;
use Zikula\PermissionsModule\ZikulaPermissionsModule;
use Zikula\RoutesModule\ZikulaRoutesModule;
use Zikula\SearchModule\ZikulaSearchModule;
use Zikula\SecurityCenterModule\ZikulaSecurityCenterModule;
use Zikula\SettingsModule\ZikulaSettingsModule;
use Zikula\ThemeModule\AbstractTheme;
use Zikula\ThemeModule\Engine\Engine as ThemeEngine;
use Zikula\ThemeModule\EventListener\AddJSConfigListener;
use Zikula\ThemeModule\ZikulaThemeModule;
use Zikula\UsersModule\ZikulaUsersModule;
use Zikula\ZAuthModule\ZikulaZAuthModule;

// Defines for access levels
define('ACCESS_INVALID', -1);
define('ACCESS_NONE', 0);
define('ACCESS_OVERVIEW', 100);
define('ACCESS_READ', 200);
define('ACCESS_COMMENT', 300);
define('ACCESS_MODERATE', 400);
define('ACCESS_EDIT', 500);
define('ACCESS_ADD', 600);
define('ACCESS_DELETE', 700);
define('ACCESS_ADMIN', 800);

abstract class ZikulaKernel extends Kernel implements ZikulaHttpKernelInterface
{
    public const VERSION = '3.0.0';

    public const PHP_MINIMUM_VERSION = '7.2.0';

    /**
     * The parameter name identifying the currently installed version of the core.
     */
    public const CORE_INSTALLED_VERSION_PARAM = 'core_installed_version';

    /**
     * The controller at the front of the application (the first file loaded as controlled by the server & .htaccess)
     * @see src/.htaccess
     * @see AddJSConfigListener::addJSConfig
     */
    public const FRONT_CONTROLLER = 'index.php';

    /**
     * Public list of core modules and their bundle class.
     * @var array
     */
    public static $coreModules = [
        'ZikulaAdminModule' => ZikulaAdminModule::class,
        'ZikulaBlocksModule' => ZikulaBlocksModule::class,
        'ZikulaCategoriesModule' => ZikulaCategoriesModule::class,
        'ZikulaExtensionsModule' => ZikulaExtensionsModule::class,
        'ZikulaGroupsModule' => ZikulaGroupsModule::class,
        'ZikulaMailerModule' => ZikulaMailerModule::class,
        'ZikulaMenuModule' => ZikulaMenuModule::class,
        'ZikulaPermissionsModule' => ZikulaPermissionsModule::class,
        'ZikulaRoutesModule' => ZikulaRoutesModule::class,
        'ZikulaSearchModule' => ZikulaSearchModule::class,
        'ZikulaSecurityCenterModule' => ZikulaSecurityCenterModule::class,
        'ZikulaSettingsModule' => ZikulaSettingsModule::class,
        'ZikulaThemeModule' => ZikulaThemeModule::class,
        'ZikulaUsersModule' => ZikulaUsersModule::class,
        'ZikulaZAuthModule' => ZikulaZAuthModule::class
    ];

    /**
     * @var boolean
     */
    private $dump = true;

    /**
     * @var array
     */
    private $modules = [];

    /**
     * @var array
     */
    private $themes = [];

    /**
     * @var ClassLoader
     */
    private $autoloader;

    public function setDump(bool $flag): void
    {
        $this->dump = $flag;
    }

    public function boot()
    {
        if (null === $this->autoloader) {
            $this->getAutoloader();
        }

        parent::boot();

        foreach ($this->bundles as $name => $bundle) {
            if ($bundle instanceof AbstractModule && !isset($this->modules[$name])) {
                $this->modules[$name] = $bundle;
            } elseif ($bundle instanceof AbstractTheme && !isset($this->themes[$name])) {
                $this->themes[$name] = $bundle;
            }
        }
    }

    public function getModule(string $moduleName): AbstractModule
    {
        if (!isset($this->modules[$moduleName])) {
            throw new InvalidArgumentException(sprintf('Module "%s" does not exist or it is not enabled.', $moduleName));
        }

        return $this->modules[$moduleName];
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public static function isCoreModule(string $moduleName): bool
    {
        return array_key_exists($moduleName, self::$coreModules);
    }

    public function getTheme(string $themeName): AbstractTheme
    {
        if (!isset($this->themes[$themeName])) {
            throw new InvalidArgumentException(sprintf('Theme "%s" does not exist or it is not enabled.', $themeName));
        }

        return $this->themes[$themeName];
    }

    public function getThemes(): array
    {
        return $this->themes;
    }

    public function getJustBundles(): array
    {
        $bundles = [];
        foreach ($this->bundles as $bundle) {
            if (!$bundle instanceof AbstractBundle) {
                $bundles[] = $bundle;
            }
        }

        return $bundles;
    }

    public function isBundle(string $name): bool
    {
        try {
            $this->getBundle($name);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    public function setAutoloader(ClassLoader $autoloader): void
    {
        $this->autoloader = $autoloader;
    }

    public function getAutoloader(): ClassLoader
    {
        if (null === $this->autoloader) {
            $loaders = spl_autoload_functions();
            if ($loaders[0][0] instanceof DebugClassLoader) {
                $classLoader = $loaders[0][0]->getClassLoader();
                if (is_callable($classLoader) && is_object($classLoader[0])) {
                    $this->autoloader = $classLoader[0];
                } elseif (is_object($classLoader)) {
                    $this->autoloader = $classLoader;
                }
            } else {
                $this->autoloader = $loaders[0][0];
            }
        }

        return $this->autoloader;
    }

    public function getConnectionConfig(): array
    {
        $config = Yaml::parse(file_get_contents($this->getProjectDir() . '/app/config/parameters.yml'));
        if (is_readable($file = $this->getProjectDir() . '/app/config/custom_parameters.yml')) {
            $config = array_merge($config, Yaml::parse(file_get_contents($file)));
        }

        return $config;
    }

    public function isClassInBundle(string $class): bool
    {
        /* @var BundleInterface $bundle */
        foreach ($this->getBundles() as $bundle) {
            if (0 === mb_strpos($class, $bundle->getNamespace())) {
                return $bundle instanceof AbstractBundle;
            }
        }

        return false;
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     *
     * Overridden not to dump the container.
     */
    protected function initializeContainer()
    {
        if (true === $this->dump) {
            return parent::initializeContainer();
        }

        $this->container = $this->buildContainer();
        $this->container->set('kernel', $this);
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        $extensions = [];
        foreach ($this->bundles as $bundle) {
            if ($bundle instanceof AbstractBundle && AbstractBundle::STATE_ACTIVE !== $bundle->getState()) {
                continue;
            }
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }

            if ($this->debug) {
                $container->addObjectResource($bundle);
            }
        }

        foreach ($this->bundles as $bundle) {
            if ($bundle instanceof AbstractBundle && AbstractBundle::STATE_ACTIVE !== $bundle->getState()) {
                continue;
            }
            $bundle->build($container);
        }

        $this->build($container);

        foreach ($container->getExtensions() as $extension) {
            $extensions[] = $extension->getAlias();
        }

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
    }

    public function locateResource($name, $dir = null, $first = true)
    {
        $locations = parent::locateResource($name, $dir, false);
        if ($locations && null !== $dir && false !== mb_strpos($locations[0], $dir)) {
            // if found in $dir (typically app/Resources) return it immediately.
            return $locations[0];
        }

        $themeBundle = $this->container->get(ThemeEngine::class)->getTheme();
        // add theme path to template locator
        // this method functions if the controller uses `@Template` or `ZikulaSpecModule:Foo:index.html.twig` naming scheme
        // if `@ZikulaSpecModule/Foo/index.html.twig` (name-spaced) naming scheme is used
        // the \Zikula\ThemeModule\EventListener\TemplatePathOverrideListener::setUpThemePathOverrides method is used instead
        if ($themeBundle && false === mb_strpos($name, $themeBundle->getName())) {
            // do not add theme override path to theme files
            $customThemePath = $themeBundle->getPath() . '/Resources';

            return parent::locateResource($name, $customThemePath);
        }

        return $locations[0];
    }
}
