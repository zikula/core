<?php

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class ZikulaKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            //new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            //new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Zikula\Bundle\CoreBundle\CoreBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
        );

        $this->registerCoreModules($bundles);

        $bundles[] = new CustomBundle\CustomBundle();

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        $loader->load(__DIR__.'/config/parameters.yml');
        if (is_readable(__DIR__.'/config/personal_parameters.yml')) {
            $loader->load(__DIR__.'/config/personal_parameters.yml');
        }
    }

    private function registerCoreModules(array &$bundles)
    {
        $bundles[] = new Blocks\BlocksModule();
        $bundles[] = new Categories\CategoriesModule();
        $bundles[] = new Errors\ErrorsModule();
        $bundles[] = new Extensions\ExtensionsModule();
        $bundles[] = new Groups\GroupsModule();
        $bundles[] = new Mailer\MailerModule();
        $bundles[] = new PageLock\PageLockModule();
        $bundles[] = new Permissions\PermissionsModule();
        $bundles[] = new Search\SearchModule();
        $bundles[] = new SecurityCenter\SecurityCenterModule();
        $bundles[] = new Settings\SettingsModule();
        $bundles[] = new Theme\ThemeModule();
        $bundles[] = new Users\UsersModule();
        $bundles[] = new Andreas08\Andreas08Theme();
    }
}
