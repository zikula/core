<?php

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Zikula\Bundle\CoreBundle\CoreBundle(),
//            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
//            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
//            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Zikula\Bundle\ModuleBundle\ZikulaModuleBundle(),
            new Zikula\Bundle\ThemeBundle\ZikulaThemeBundle(),
            new AdminModule\ZikulaAdminModule(),
            new BlocksModule\ZikulaBlocksModule(),
            new CategoriesModule\ZikulaCategoriesModule(),
            new ErrorsModule\ZikulaErrorsModule(),
            new ExtensionsModule\ZikulaExtensionsModule(),
            new GroupsModule\ZikulaGroupsModule(),
            new MailerModule\ZikulaMailerModule(),
            new PageLockModule\ZikulaPageLockModule(),
            new PermissionsModule\ZikulaPermissionsModule(),
            new SearchModule\ZikulaSearchModule(),
            new SecurityCenterModule\ZikulaSecurityCenterModule(),
            new SettingsModule\ZikulaSettingsModule(),
            new ThemeModule\ZikulaThemeModule(),
            new UsersModule\ZikulaUsersModule(),

        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        $loader->load(__DIR__.'/config/database.yml');
    }
}
