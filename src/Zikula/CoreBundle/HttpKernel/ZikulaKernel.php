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

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Exception;
use InvalidArgumentException;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Zikula\AdminModule\ZikulaAdminModule;
use Zikula\AtomTheme\ZikulaAtomTheme;
use Zikula\BlocksModule\ZikulaBlocksModule;
use Zikula\BootstrapTheme\ZikulaBootstrapTheme;
use Zikula\CategoriesModule\ZikulaCategoriesModule;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\AbstractModule;
use Zikula\ExtensionsModule\AbstractTheme;
use Zikula\ExtensionsModule\ZikulaExtensionsModule;
use Zikula\GroupsModule\ZikulaGroupsModule;
use Zikula\MailerModule\ZikulaMailerModule;
use Zikula\MenuModule\ZikulaMenuModule;
use Zikula\PermissionsModule\ZikulaPermissionsModule;
use Zikula\PrinterTheme\ZikulaPrinterTheme;
use Zikula\RoutesModule\ZikulaRoutesModule;
use Zikula\RssTheme\ZikulaRssTheme;
use Zikula\SearchModule\ZikulaSearchModule;
use Zikula\SecurityCenterModule\ZikulaSecurityCenterModule;
use Zikula\SettingsModule\ZikulaSettingsModule;
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
    public const VERSION = '3.1.0-DEV';

    public const PHP_MINIMUM_VERSION = '7.2.5';

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
    public static $coreExtension = [
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
        'ZikulaZAuthModule' => ZikulaZAuthModule::class,
        'ZikulaAtomTheme' => ZikulaAtomTheme::class,
        'ZikulaBootstrapTheme' => ZikulaBootstrapTheme::class,
        'ZikulaPrinterTheme' => ZikulaPrinterTheme::class,
        'ZikulaRssTheme' => ZikulaRssTheme::class
    ];

    /**
     * @var array
     */
    private $modules = [];

    /**
     * @var array
     */
    private $themes = [];

    /**
     * @var callable
     */
    private $autoloader;

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

    public static function isCoreExtension(string $extensionName): bool
    {
        return array_key_exists($extensionName, self::$coreExtension);
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
            if (!$bundle instanceof AbstractExtension) {
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

    public function setAutoloader(callable $autoloader): void
    {
        $this->autoloader = $autoloader;
    }

    public function getAutoloader(): object
    {
        if (null === $this->autoloader) {
            $loaders = spl_autoload_functions();
            foreach ($loaders as $loader) {
                if ($loader[0] instanceof DebugClassLoader) {
                    $classLoader = $loader[0]->getClassLoader();
                    if ($classLoader instanceof Closure) {
                        // skip unwanted autoloaders ("Cannot use object of type Closure as array")
                        continue;
                    }
                    if (is_callable($classLoader) && is_object($classLoader[0])) {
                        $this->autoloader = $classLoader[0];
                    } elseif (is_object($classLoader)) {
                        $this->autoloader = $classLoader;
                    }
                } else {
                    $this->autoloader = $loader[0];
                }
            }
        }

        return $this->autoloader;
    }

    public function isClassInBundle(string $class): bool
    {
        /* @var BundleInterface $bundle */
        foreach ($this->getBundles() as $bundle) {
            if (0 === mb_strpos($class, $bundle->getNamespace())) {
                return $bundle instanceof AbstractExtension;
            }
        }

        return false;
    }
}
