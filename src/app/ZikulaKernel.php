<?php

use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class ZikulaKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Zikula\Bundle\CoreBundle\CoreBundle(),
            new Zikula\Bundle\JQueryBundle\ZikulaJQueryBundle(),
            new Zikula\Bundle\JQueryUIBundle\ZikulaJQueryUIBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        );

        $this->registerCoreModules($bundles);

        $bundles[] = new CustomBundle\CustomBundle();

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Zikula\Bundle\GeneratorBundle\ZikulaGeneratorBundle();
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
        $bundles[] = new Zikula\Module\AdminModule\ZikulaAdminModule();
        $bundles[] = new Zikula\Module\BlocksModule\ZikulaBlocksModule();
        $bundles[] = new Zikula\Module\CategoriesModule\ZikulaCategoriesModule();
        $bundles[] = new Zikula\Module\ExtensionsModule\ZikulaExtensionsModule();
        $bundles[] = new Zikula\Module\GroupsModule\ZikulaGroupsModule();
        $bundles[] = new Zikula\Module\MailerModule\ZikulaMailerModule();
        $bundles[] = new Zikula\Module\PageLockModule\ZikulaPageLockModule();
        $bundles[] = new Zikula\Module\PermissionsModule\ZikulaPermissionsModule();
        $bundles[] = new Zikula\Module\SearchModule\ZikulaSearchModule();
        $bundles[] = new Zikula\Module\SecurityCenterModule\ZikulaSecurityCenterModule();
        $bundles[] = new Zikula\Module\SettingsModule\ZikulaSettingsModule();
        $bundles[] = new Zikula\Module\ThemeModule\ZikulaThemeModule();
        $bundles[] = new Zikula\Module\UsersModule\ZikulaUsersModule();
        $bundles[] = new Zikula\RoutesModule\ZikulaRoutesModule();
//        $bundles[] = new Zikula\Theme\Andreas08Theme\ZikulaAndreas08Theme();
//        $bundles[] = new Zikula\Theme\AtomTheme\ZikulaAtomTheme();
//        $bundles[] = new Zikula\Theme\RssTheme\ZikulaRssTheme();
//        $bundles[] = new Zikula\Theme\PrinterTheme\ZikulaPrinterTheme();
//        $bundles[] = new Zikula\Theme\MobileTheme\ZikulaMobileTheme();
//        $bundles[] = new Zikula\Theme\SeaBreezeTheme\ZikulaSeaBreezeTheme();

        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $boot->getPersistedBundles($this, $bundles);
    }
}
