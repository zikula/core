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
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Zikula\Bundle\CoreBundle\ZikulaCoreBundle(),
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
        if (is_readable(__DIR__.'/config/custom_parameters.yml')) {
            $loader->load(__DIR__.'/config/custom_parameters.yml');
        }
    }

    private function registerCoreModules(array &$bundles)
    {
        //$bundles[] = new AdminModule\AdminModule();
        $bundles[] = new BlocksModule\BlocksModule();
        $bundles[] = new Categories\CategoriesModule();
        $bundles[] = new ErrorsModule\ErrorsModule();
        $bundles[] = new ExtensionsModule\ExtensionsModule();
        $bundles[] = new GroupsModule\GroupsModule();
        $bundles[] = new Zikula\Module\MailerModule\ZikulaMailerModule();
        $bundles[] = new PageLockModule\PageLockModule();
        $bundles[] = new PermissionsModule\PermissionsModule();
        $bundles[] = new SearchModule\SearchModule();
        $bundles[] = new SecurityCenterModule\SecurityCenterModule();
        $bundles[] = new SettingsModule\SettingsModule();
        $bundles[] = new ThemeModule\ThemeModule();
        $bundles[] = new UsersModule\UsersModule();
        $bundles[] = new Andreas08Theme\Andreas08Theme();
    }
}
