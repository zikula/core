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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\Helper\PersistedBundleHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class Kernel extends ZikulaKernel
{
    use MicroKernelTrait;

    /**
     * @var string
     */
    private $databaseUrl;

    public function __construct(string $environment, bool $debug, string $databaseUrl = '')
    {
        parent::__construct($environment, $debug);

        $this->databaseUrl = $databaseUrl;
    }

    public function registerBundles(): iterable
    {
        $bundleHelper = new PersistedBundleHelper($this->databaseUrl);
        $bundles = require $this->getProjectDir() . '/config/bundles.php';
        $bundleHelper->getPersistedBundles($this, $bundles);

        foreach ($bundles as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir() . '/config/';

        $container->import($configDir . '{packages}/*.yaml');
        $container->import($configDir . '{packages}/' . $this->environment . '/*.yaml');

        if (is_file($configDir . 'services.yaml')) {
            $container->import($configDir . '{services}.yaml');
            $container->import($configDir . '{services}_' . $this->environment . '.yaml');
        } elseif (is_file($path = $configDir . 'services.php')) {
            (require $path)($container->withPath($path), $this);
        }

        if (is_file($configDir . 'services_custom.yaml')) {
            $loader->load($configDir . 'services_custom.yaml');
        }

        if (!is_file($configDir . DynamicConfigDumper::CONFIG_GENERATED)) {
            // There is no generated configuration (yet), load default values.
            // This only happens at the very first time Symfony is started.
            $loader->load($configDir . DynamicConfigDumper::CONFIG_DEFAULT);
        } else {
            $loader->load($configDir . DynamicConfigDumper::CONFIG_GENERATED);
            if (is_file($configDir . 'dynamic/generated_' . $this->environment . '.yaml')) {
                $loader->load($configDir . 'dynamic/generated_' . $this->environment . '.yaml');
            }
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getProjectDir() . '/config/';

        $routes->import($configDir . '{routes}/' . $this->environment . '/*.yaml');
        $routes->import($configDir . '{routes}/*.yaml');
        if (is_file($configDir . 'routes.yaml')) {
            $routes->import($configDir . '{routes}.yaml');
        } elseif (is_file($path = $configDir . 'routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}
