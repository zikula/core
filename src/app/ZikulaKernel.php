<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

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

class ZikulaKernel extends Kernel
{
    const VERSION = '1.4.6';

    const VERSION_SUB = 'Overture';

    const PHP_MINIMUM_VERSION = '5.4.1';

    /**
     * The parameter name identifying the currently installed version of the core.
     */
    const CORE_INSTALLED_VERSION_PARAM = 'core_installed_version';

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

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Zikula\Bundle\CoreBundle\CoreBundle(),
            new Zikula\Bundle\CoreInstallerBundle\ZikulaCoreInstallerBundle(),
            new Zikula\Bundle\FormExtensionBundle\ZikulaFormExtensionBundle(),
            new Zikula\Bundle\HookBundle\ZikulaHookBundle(),
            new Zikula\Bundle\JQueryBundle\ZikulaJQueryBundle(),
            new Zikula\Bundle\JQueryUIBundle\ZikulaJQueryUIBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Matthias\SymfonyConsoleForm\Bundle\SymfonyConsoleFormBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
        ];

        $this->registerCoreModules($bundles);

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
//            $bundles[] = new Zikula\Bundle\GeneratorBundle\ZikulaGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->rootDir.'/config/config_'.$this->getEnvironment().'.yml');
        $loader->load($this->rootDir.'/config/parameters.yml');
        if (is_readable($this->rootDir.'/config/custom_parameters.yml')) {
            $loader->load($this->rootDir.'/config/custom_parameters.yml');
        }

        if (!is_readable($this->rootDir . '/config/' . DynamicConfigDumper::CONFIG_GENERATED)) {
            // There is no generated configuration (yet), load default values.
            // This only happens at the very first time Symfony is started.
            $loader->load($this->rootDir . '/config/' . DynamicConfigDumper::CONFIG_DEFAULT);
        } else {
            $loader->load($this->rootDir . '/config/' . DynamicConfigDumper::CONFIG_GENERATED);
        }
    }

    private function registerCoreModules(array &$bundles)
    {
        foreach (self::$coreModules as $bundleClass) {
            $bundles[] = new $bundleClass();
        }

        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $boot->getPersistedBundles($this, $bundles);
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

    /**
     * Checks if name is is the list of core modules.
     * @param $moduleName
     * @return bool
     */
    public static function isCoreModule($moduleName)
    {
        return array_key_exists($moduleName, self::$coreModules);
    }
}
