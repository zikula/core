<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Symfony\Component\Filesystem\Filesystem;

class ConfigHelper
{
    private $kernel;

    private $params;

    public function __construct(ZikulaKernel $kernel)
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
        $search = ["#\['$searchKey'\]\s*=\s*('|\")(.*)('|\")\s*;#", "#\['$searchKey'\]\s*=\s*(\d)\s*;#"];
        $replace = ["['$searchKey'] = '$replaceWith';", "['$searchKey'] = $replaceWith;"];

        return preg_replace($search, $replace, $string);
    }
}
