<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigDumper.
 */
class YamlDumper
{
    protected $fs;
    protected $fullPath;

    public function __construct($configDir, $filePath = 'custom_parameters.yml', $initCopy = null)
    {
        $this->fullPath = $configDir . DIRECTORY_SEPARATOR . $filePath;
        $this->fs = new Filesystem();
        if (!empty($initCopy)) {
            if (!$this->fs->exists($this->fullPath)) {
                // initialize file from a copy of original
                $this->fs->copy($configDir . DIRECTORY_SEPARATOR . $initCopy, $this->fullPath);
            }
        }
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter's name
     * @param bool   $value The parameter's value
     */
    public function setParameter($name, $value)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        $configuration['parameters'][$name] = $value;
        $this->dumpFile($configuration);
    }

    /**
     * Set all the parameters.
     *
     * @param $params
     */
    public function setParameters($params)
    {
        foreach ($params as $name => $value) {
            $this->validateName($name, true);
        }
        $configuration = $this->parseFile();
        $configuration['parameters'] = $params;
        $this->dumpFile($configuration);
    }

    /**
     * Returns a parameter.
     *
     * @param string $name The requested parameter's name
     *
     * @return mixed The parameter value
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
     * Return all the parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        $configuration = $this->parseFile();
        if (isset($configuration['parameters'])) {
            return $configuration['parameters'];
        }

        return [];
    }

    /**
     * Deletes a parameter.
     *
     * @param string $name The parameter's name
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
     * @param string $name  The configuration's name
     * @param mixed $value The configuration's value
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
     * @param string $name The requested configuration's name
     *
     * @return mixed The configuration value
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
     * @param string $name The configuration's name
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
     * @param string $name The requested configuration's name
     * @return string
     */
    public function getConfigurationForHtml($name)
    {
        $config = $this->getConfiguration($name);
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
    protected function formatValue($value)
    {
        if (null === $value) {
            return '<i>null</i>';
        }

        $html = '';

        foreach ($value as $key => $val) {
            $html .= '<li><strong>' . htmlspecialchars($key, ENT_QUOTES) . ':</strong>';
            if (is_array($val)) {
                $html .= $this->formatValue($val) . "</li>\n";
            } else {
                $val = !empty($val) ? htmlspecialchars($val, ENT_QUOTES) : '<em>null</em>';
                $html .= " " . $val . "</li>\n";
            }
        }

        return "<ul>\n$html</ul>\n";
    }

    /**
     * Parses a Yaml file and return a configuration array.
     *
     * @return array The configuration array
     */
    protected function parseFile()
    {
        if (!$this->fs->exists($this->fullPath)) {
            return [];
        }

        return Yaml::parse(file_get_contents($this->fullPath));
    }

    /**
     * Dump configuration into dynamic configuration file.
     *
     * @param array $configuration The configuration array to dump
     */
    protected function dumpFile($configuration)
    {
        $yaml = Yaml::dump($configuration);
        $this->fs->dumpFile($this->fullPath, $yaml);
    }

    /**
     * Validates that the configuration / parameter name is correct.
     *
     * @param string $name        The requested configuration / parameter name
     * @param bool   $isParameter Whether or not a parameter is requested
     *
     * @throws \InvalidArgumentException Thrown if the configuration / parameter is invalid
     */
    protected function validateName($name, $isParameter)
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
