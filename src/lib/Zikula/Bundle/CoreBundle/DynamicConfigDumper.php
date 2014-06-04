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

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DynamicConfigDumper.
 */
class DynamicConfigDumper
{
    private $configDir;

    private $fs;

    const CONFIG_GENERATED = 'dynamic/generated.yml';

    const CONFIG_DEFAULT = 'dynamic/default.yml';

    public function __construct($configDir)
    {
        $this->configDir = $configDir . DIRECTORY_SEPARATOR;
        $this->fs = new Filesystem();

        if (!$this->fs->exists($this->configDir . self::CONFIG_GENERATED)) {
            // This class is called for the very first time. Make a copy of the default configuration file and safe
            // it as generated configuration file.
            $this->fs->copy($this->configDir . self::CONFIG_DEFAULT, $this->configDir . self::CONFIG_GENERATED);
        }
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter's name.
     * @param bool   $value The parameter's value.
     */
    public function setParameter($name, $value)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        $configuration['parameters'][$name] = $value;
        $this->dumpFile($configuration);
    }

    /**
     * Returns a parameter.
     *
     * @param string $name The requested parameter's name.
     *
     * @return mixed The parameter value.
     */
    public function getParameter($name)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        if (isset($configuration['parameters'][$name])) {
            return $configuration['parameters'][$name];
        }

        return null;
    }

    /**
     * Deletes a parameter.
     *
     * @param string $name The parameter's name.
     */
    public function delParameter($name)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        unset($configuration['parameters'][$name]);
        $this->dumpFile($configuration);
    }

    /**
     * Sets a configuration.
     *
     * @param string $name  The configuration's name.
     * @param bool   $value The configuration's value.
     */
    public function setConfiguration($name, $value)
    {
        $this->validateName($name, false);
        $configuration = $this->parseFile();
        $configuration[$name] = $value;
        $this->dumpFile($configuration);
    }

    /**
     * Returns a configuration.
     *
     * @param string $name The requested configuration's name.
     *
     * @return mixed The configuration value.
     */
    public function getConfiguration($name)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        if (isset($configuration[$name])) {
            return $configuration[$name];
        }

        return null;
    }

    /**
     * Deletes a configuration.
     *
     * @param string $name The configuration's name.
     */
    public function delConfiguration($name)
    {
        $this->validateName($name, false);
        $configuration = $this->parseFile();
        unset($configuration[$name]);
        $this->dumpFile($configuration);
    }

    /**
     * Returns configuration in html format.
     *
     * @param string $name         The requested configuration's name.
     * @param bool   $fetchDefault Whether or not to return the values specified in the dynamic_config_default.yml file
     *                             if no configuration is set in the dynamic_config.yml file.
     *
     * @return string
     */
    public function getConfigurationForHtml($name, $fetchDefault = true)
    {
        $config = $this->getConfiguration($name, $fetchDefault);
        $html = $this->formatValue($config);

        return $html;
    }

    /**
     * Formats a value for html (recursive array safe).
     *
     * @param $value
     *
     * @return string
     */
    private function formatValue($value)
    {
        if ($value === null) {
            return "<i>null</i>";
        }

        $html = "";

        foreach ($value as $key => $value) {
            $html .= "<dt>" . \DataUtil::formatForDisplay($key) . ":";
            if (is_array($value)) {
                $html .= "</dt><dd>" . $this->formatValue($value) . "</dd>";
            } else {
                $html .= " " . \DataUtil::formatForDisplay($value) . "</dt><dd></dd>";
            }
        }

        return "<dl>$html</dl>";
    }

    /**
     * Parses a Yaml file and return a configuration array.
     *
     * @return array The configuration array.
     */
    private function parseFile()
    {
        $file = $this->configDir . self::CONFIG_GENERATED;
        if (!$this->fs->exists($file)) {
            return array();
        }

        return Yaml::parse(file_get_contents($file));
    }

    /**
     * Dump configuration into dynamic configuration file.
     *
     * @param array $configuration The configuration array to dump.
     */
    private function dumpFile($configuration)
    {
        $yaml = "#This is a dynamically generated configuration file. Do not touch!\n\n" . Yaml::dump($configuration);
        $this->fs->dumpFile($this->configDir . self::CONFIG_GENERATED, $yaml);
    }

    /**
     * Validates that the configuration / parameter name is correct.
     *
     * @param string $name        The requested configuration / parameter name.
     * @param bool   $isParameter Whether or not a parameter is requested.
     *
     * @throws \InvalidArgumentException Thrown if the configuration / parameter is invalid.
     */
    private function validateName($name, $isParameter)
    {
        if (!is_string($name) || strlen($name) <= 0 || (!$isParameter && $name == 'parameters')) {
            if ($isParameter) {
                throw new \InvalidArgumentException('The parameter name must be a string');
            } else {
                throw new \InvalidArgumentException('The configuration name must not be "parameters" and must be a string');
            }
        }
    }
} 
