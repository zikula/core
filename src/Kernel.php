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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
//use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\Helper\PersistedBundleHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class Kernel extends ZikulaKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundleHelper = new PersistedBundleHelper();
        $bundles = require $this->getProjectDir() . '/config/bundles.php';
        $bundleHelper->getPersistedBundles($this, $bundles);
        foreach ($bundles as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    // could be remove when upgrading Symfony to 5.1.0 (wanted?)
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    // use new signature when upgrading Symfony to 5.1.0
    //protected function configureContainer(ContainerConfigurator $container): void
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        // remove when upgrading to Symfony 5.1.0
        $container->setParameter('kernel.secret', '%env(APP_SECRET)%');

        /** use with Symfony 5.1.0
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');
        $container->import('../config/{services}.yaml');
        $container->import('../config/{services}_' . $this->environment . '.yaml');
         */
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', $this->debug);
        $container->setParameter('container.dumper.inline_factories', true);
        $configDir = $this->getProjectDir() . '/config/';

        $loader->load($configDir . '{packages}/*.yaml', 'glob');
        $loader->load($configDir . '{packages}/' . $this->environment . '/*.yaml', 'glob');
        $loader->load($configDir . '{services}.yaml', 'glob');
        $loader->load($configDir . '{services}_' . $this->environment . '.yaml', 'glob');

        if (is_readable($configDir . 'services_custom.yaml')) {
            $loader->load($configDir . 'services_custom.yaml');
        }

        if (!is_readable($configDir . DynamicConfigDumper::CONFIG_GENERATED)) {
            // There is no generated configuration (yet), load default values.
            // This only happens at the very first time Symfony is started.
            $loader->load($configDir . DynamicConfigDumper::CONFIG_DEFAULT);
        } else {
            $loader->load($configDir . DynamicConfigDumper::CONFIG_GENERATED);
        }
    }

    // use new signature when upgrading Symfony to 5.1.0
    //protected function configureRoutes(RoutingConfigurator $routes): void
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $configDir = $this->getProjectDir() . '/config/';

        /** use with Symfony 5.1.0
        $routes->import($configDir . '{routes}/' . $this->environment . '/*.yaml');
        $routes->import($configDir . '{routes}/*.yaml');
        $routes->import($configDir . '{routes}.yaml');
         */

        $routes->import($configDir . '{routes}/' . $this->environment . '/*.yaml', '/', 'glob');
        $routes->import($configDir . '{routes}/*.yaml', '/', 'glob');
        $routes->import($configDir . '{routes}.yaml', '/', 'glob');
    }
}
