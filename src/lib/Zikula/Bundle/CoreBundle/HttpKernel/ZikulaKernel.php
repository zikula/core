<?php

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
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\ThemeModule\AbstractTheme;

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
    const VERSION = '3.0.0';

    const VERSION_SUB = 'Concerto';

    const PHP_MINIMUM_VERSION = '7.2.0';

    /**
     * The parameter name identifying the currently installed version of the core.
     */
    const CORE_INSTALLED_VERSION_PARAM = 'core_installed_version';

    /**
     * The controller at the front of the application (the first file loaded as controlled by the server & .htaccess)
     * @see src/.htaccess
     * @see \Zikula\ThemeModule\EventListener\AddJSConfigListener::addJSConfig
     */
    const FRONT_CONTROLLER = 'index.php';

    /**
     * Public list of core modules and their bundle class.
     * @var array
     */
    public static $coreModules = [
        'ZikulaAdminModule' => 'Zikula\AdminModule\ZikulaAdminModule',
        'ZikulaBlocksModule' => 'Zikula\BlocksModule\ZikulaBlocksModule',
        'ZikulaCategoriesModule' => 'Zikula\CategoriesModule\ZikulaCategoriesModule',
        'ZikulaExtensionsModule' => 'Zikula\ExtensionsModule\ZikulaExtensionsModule',
        'ZikulaGroupsModule' => 'Zikula\GroupsModule\ZikulaGroupsModule',
        'ZikulaMailerModule' => 'Zikula\MailerModule\ZikulaMailerModule',
        'ZikulaPermissionsModule' => 'Zikula\PermissionsModule\ZikulaPermissionsModule',
        'ZikulaRoutesModule' => 'Zikula\RoutesModule\ZikulaRoutesModule',
        'ZikulaSearchModule' => 'Zikula\SearchModule\ZikulaSearchModule',
        'ZikulaSecurityCenterModule' => 'Zikula\SecurityCenterModule\ZikulaSecurityCenterModule',
        'ZikulaSettingsModule' => 'Zikula\SettingsModule\ZikulaSettingsModule',
        'ZikulaThemeModule' => 'Zikula\ThemeModule\ZikulaThemeModule',
        'ZikulaUsersModule' => 'Zikula\UsersModule\ZikulaUsersModule',
        'ZikulaZAuthModule' => 'Zikula\ZAuthModule\ZikulaZAuthModule',
        'ZikulaMenuModule' => 'Zikula\MenuModule\ZikulaMenuModule',
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

    /**
     * Flag determines if container is dumped or not
     *
     * @param $flag
     */
    public function setDump($flag)
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

    /**
     * Get named module bundle.
     *
     * @param string  $moduleName
     * @param boolean $first
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     * @return AbstractModule
     */
    public function getModule($moduleName, $first = true)
    {
        if (!isset($this->modules[$moduleName])) {
            throw new \InvalidArgumentException(sprintf('Module "%s" does not exist or it is not enabled.', $moduleName, get_class($this)));
        }

        if (true === $first) {
            return $this->modules[$moduleName][0];
        }

        return $this->modules[$moduleName];
    }

    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Checks if name is is the list of core modules.
     * @param $moduleName
     * @return bool
     */
    public static function isCoreModule($moduleName)
    {
        return array_key_exists($moduleName, self::$coreModules);
    }

    /**
     * Get named theme bundle.
     *
     * @param string  $themeName
     * @param boolean $first
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     * @return AbstractTheme
     */
    public function getTheme($themeName, $first = true)
    {
        if (!isset($this->themes[$themeName])) {
            throw new \InvalidArgumentException(sprintf('Theme "%s" does not exist or it is not enabled.', $themeName, get_class($this)));
        }

        if (true === $first) {
            return $this->themes[$themeName][0];
        }

        return $this->themes[$themeName];
    }

    public function getThemes()
    {
        return $this->themes;
    }

    public function getJustBundles()
    {
        $bundles = [];
        foreach ($this->bundles as $bundle) {
            if (!$bundle instanceof AbstractBundle) {
                $bundles[] = $bundle;
            }
        }

        return $bundles;
    }

    /**
     * Is this a Bundle?
     *
     * @param $name
     * @param bool $first
     * @return bool
     */
    public function isBundle($name, $first = true)
    {
        try {
            $this->getBundle($name, $first);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function setAutoloader(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    public function getAutoloader()
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

    public function getConnectionConfig()
    {
        $config = Yaml::parse(file_get_contents($this->rootDir . '/config/parameters.yml'));
        if (is_readable($file = $this->rootDir . '/config/custom_parameters.yml')) {
            $config = array_merge($config, Yaml::parse(file_get_contents($file)));
        }

        return $config;
    }

    public function isClassInBundle($class)
    {
        /* @var BundleInterface $bundle */
        foreach ($this->getBundles() as $bundle) {
            if (0 === strpos($class, $bundle->getNamespace())) {
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
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        $extensions = [];
        foreach ($this->bundles as $bundle) {
            if ($bundle instanceof AbstractBundle && AbstractBundle::STATE_ACTIVE != $bundle->getState()) {
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
            if ($bundle instanceof AbstractBundle && AbstractBundle::STATE_ACTIVE != $bundle->getState()) {
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

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException if a custom resource is hidden by a resource in a derived bundle
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        $locations = parent::locateResource($name, $dir, false);
        if ($locations && (false !== strpos($locations[0], $dir))) {
            // if found in $dir (typically app/Resources) return it immediately.
            return $locations[0];
        }

        $themeBundle = $this->container->get('zikula_core.common.theme_engine')->getTheme();
        // add theme path to template locator
        // this method functions if the controller uses `@Template` or `ZikulaSpecModule:Foo:index.html.twig` naming scheme
        // if `@ZikulaSpecModule/Foo/index.html.twig` (name-spaced) naming scheme is used
        // the \Zikula\Bundle\CoreBundle\EventListener\Theme\TemplatePathOverrideListener::setUpThemePathOverrides method is used instead
        if ($themeBundle && (false === strpos($name, $themeBundle->getName()))) {
            // do not add theme override path to theme files
            $customThemePath = $themeBundle->getPath() . '/Resources';

            return parent::locateResource($name, $customThemePath, true);
        }

        return $locations[0];
    }
}
