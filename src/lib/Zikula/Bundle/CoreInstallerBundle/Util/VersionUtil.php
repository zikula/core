<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;

class VersionUtil
{
    /**
     * Ensures current installed version number is written to `custom_parameters.yml`
     *
     * @param ContainerInterface $container
     * @return string
     * @throws \Exception
     * @deprecated at Core-1.4.3 scheduled for removal in Core-2.0
     */
    public static function defineCurrentInstalledCoreVersion($container)
    {
        // already set?
        if ($container->hasParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM)) {
            return;
        }

        // only required when core < 1.4.3
        try {
            $conn = $container->get('doctrine.dbal.default_connection');
            $version = unserialize($conn->fetchColumn("SELECT value FROM module_vars WHERE modname = 'ZConfig' AND name = 'Version_Num'"));

            $yamlManager = new YamlDumper($container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
            $container->setParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM, $version);
            $yamlManager->setParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM, $version); // writes to file
        } catch (\Doctrine\DBAL\Exception\TableNotFoundException $e) {
            throw new \Exception("ERROR: Could not find module_vars table. Maybe you forgot to copy it to your server, or you left a custom_parameters.yml file in place with installed: true in it.");
        }
    }
}
