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

use Symfony\Component\Config\Loader\LoaderInterface;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\Helper\PersistedBundleHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class Kernel extends ZikulaKernel
{
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

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configDir = $this->getProjectDir() . '/config/';
        $loader->load($configDir . 'config_' . $this->getEnvironment() . '.yaml');

        $loader->load($configDir . 'services.yaml');
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

    public function getProjectDir()
    {
        return __DIR__;
    }
}
