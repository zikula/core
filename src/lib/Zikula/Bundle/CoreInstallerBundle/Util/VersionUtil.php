<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
     */
    public static function defineCurrentInstalledCoreVersion($container)
    {
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