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

class VersionUtil
{
    /**
     * Get current installed version number
     *
     * @param ContainerInterface $container
     * @return string
     * @throws \Exception
     * @deprecated at Core-1.4.3 scheduled for removal in Core-2.0
     */
    public static function defineCurrentInstalledCoreVersion($container)
    {
        // first attempt to set the current version by the parameter if set.
        // this is a BC measure for Core-1.4.0 -> 1.4.3
        $currentVersionParam = $container->hasParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM) ? $container->getParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM) : null;
        if (isset($currentVersionParam)) {
            define('ZIKULACORE_CURRENT_INSTALLED_VERSION', $currentVersionParam);

            return;
        }

        $moduleTable = 'module_vars';
        try {
            $stmt = $container->get('doctrine.dbal.default_connection')->executeQuery("SELECT value FROM $moduleTable WHERE modname = 'ZConfig' AND name = 'Version_Num'");
            $result = $stmt->fetch(\PDO::FETCH_NUM);
            $version = unserialize($result[0]);
            if ((!defined('ZIKULACORE_CURRENT_INSTALLED_VERSION')) || ($version !== ZIKULACORE_CURRENT_INSTALLED_VERSION)) {
                define('ZIKULACORE_CURRENT_INSTALLED_VERSION', $version);
            }
        } catch (\Doctrine\DBAL\Exception\TableNotFoundException $e) {
            throw new \Exception("ERROR: Could not find $moduleTable table. Maybe you forgot to copy it to your server, or you left a custom_parameters.yml file in place with installed: true in it.");
        } catch (\Exception $e) {
            // now what? @todo
        }
    }
}
