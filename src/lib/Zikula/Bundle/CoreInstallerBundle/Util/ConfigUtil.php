<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
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

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;

class ConfigUtil
{
    private $kernel;
    private $params;

    function __construct(ZikulaKernel $kernel)
    {
        $this->kernel = $kernel;
        // fetch contents of app/config/parameters.yml or custom_parameters.yml
        $this->params = $this->kernel->getConnectionConfig();
    }

    /**
     * Write the legacy Config file
     * @param array $params
     * @deprecated since 1.4.0
     */
    public function writeLegacyConfig($params = null)
    {
        $personalConfigPath = realpath($this->kernel->getRootDir() . '/../config/personal_config.php');
        $configPath = realpath($this->kernel->getRootDir() . '/../config/config.php');
        if (!file_exists($personalConfigPath)) {
            $fs = new Filesystem();
            if ($fs->exists($configPath)) {
                // initialize file from a copy of original
                $fs->copy($configPath, $this->kernel->getRootDir() . '/../config/personal_config.php');
            }
            $personalConfigPath = realpath($this->kernel->getRootDir() . '/../config/personal_config.php');
        }
        $params = !empty($params) ? $params : $this->params;
        if (is_writable($personalConfigPath)) {
            $file = file_get_contents($personalConfigPath);
            $file = $this->replaceKeys('dbname', $params['database_name'], $file);
            $file = $this->replaceKeys('dbdriver', substr($params['database_driver'], 4), $file);
            $file = $this->replaceKeys('dbtabletype', $params['dbtabletype'], $file);
            $file = $this->replaceKeys('user', $params['database_user'], $file);
            $file = $this->replaceKeys('password', $params['database_password'], $file);
            $file = $this->replaceKeys('host', $params['database_host'], $file);
            file_put_contents($personalConfigPath, $file);
        } else {
            throw new AccessDeniedException('config.php');
        }
    }

    /**
     * replace keys in config.php file with new values
     * @deprecated since 1.4.0
     *
     * @param $searchKey
     * @param $replaceWith
     * @param $string
     * @return mixed
     */
    private function replaceKeys($searchKey, $replaceWith, $string)
    {
        $search = array("#\['$searchKey'\]\s*=\s*('|\")(.*)('|\")\s*;#", "#\['$searchKey'\]\s*=\s*(\d)\s*;#");
        $replace = array("['$searchKey'] = '$replaceWith';", "['$searchKey'] = $replaceWith;");
        return preg_replace($search, $replace, $string);
    }

}