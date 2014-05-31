<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DynamicConfigDumper.
 */
class DynamicConfigDumper
{
    private $configPath;

    public function __construct($configDir)
    {
        $this->configPath = "$configDir/dynamic_config.yml";
    }

    private function parseFile()
    {
        if (!file_exists($this->configPath)) {
            return array();
        }

        return Yaml::parse(file_get_contents($this->configPath));
    }

    private function dumpFile($configuration)
    {
        $yaml = "#This is a dynamically generated configuration file. Do not touch!\n\n" . Yaml::dump($configuration);

        file_put_contents($this->configPath, $yaml);
    }

    public function setParameter($name, $value)
    {
        $this->validateName($name, true);

        $configuration = $this->parseFile();
        $configuration['parameters'][$name] = $value;
        $this->dumpFile($configuration);
    }

    public function getParameter($name)
    {
        $this->validateName($name, true);

        $configuration = $this->parseFile();
        
        return isset($configuration['parameters'][$name]) ? $configuration['parameters'][$name] : null;
    }

    public function delParameter($name)
    {
        $this->validateName($name, true);

        $configuration = $this->parseFile();
        unset($configuration['parameters'][$name]);
        $this->dumpFile($configuration);
    }

    public function setConfiguration($name, $value)
    {
        $this->validateName($name, false);

        $configuration = $this->parseFile();
        $configuration[$name] = $value;
        $this->dumpFile($configuration);
    }
    
    public function getConfiguration($name)
    {
        $this->validateName($name, true);

        $configuration = $this->parseFile();
        
        return isset($configuration[$name]) ? $configuration[$name] : null;
    }

    public function delConfiguration($name)
    {
        $this->validateName($name, false);

        $configuration = $this->parseFile();
        unset($configuration[$name]);
        $this->dumpFile($configuration);
    }

    private function validateName($name, $isParameter)
    {
        return is_string($name) && strlen($name) > 0 && ($isParameter || $name != 'parameters');
    }
} 
