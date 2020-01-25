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

namespace Zikula\Bundle\CoreBundle;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlDumper.
 */
class YamlDumper
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $fullPath;

    public function __construct(string $configDir, string $filePath = 'services_custom.yaml', string $initCopy = null)
    {
        $this->fullPath = $configDir . DIRECTORY_SEPARATOR . $filePath;
        $this->fs = new Filesystem();
        if (!empty($initCopy) && !$this->fs->exists($this->fullPath)) {
            // initialize file from a copy of original
            $this->fs->copy($configDir . DIRECTORY_SEPARATOR . $initCopy, $this->fullPath);
        }
    }

    /**
     * Sets a parameter.
     */
    public function setParameter(string $name, $value): void
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        $configuration['parameters'][$name] = $value;
        $this->dumpFile($configuration);
    }

    /**
     * Set all the parameters.
     */
    public function setParameters(array $params = []): void
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
     * @return mixed The parameter value
     */
    public function getParameter(string $name)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();

        return $configuration['parameters'][$name] ?? null;
    }

    /**
     * Return all the parameters.
     */
    public function getParameters(): array
    {
        $configuration = $this->parseFile();

        return $configuration['parameters'] ?? [];
    }

    /**
     * Deletes a parameter.
     */
    public function delParameter(string $name): void
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();
        if (isset($configuration['parameters'][$name])) {
            unset($configuration['parameters'][$name]);
            $this->dumpFile($configuration);
        }
    }

    /**
     * Sets a configuration.
     */
    public function setConfiguration(string $name, $value): void
    {
        $this->validateName($name, false);
        $configuration = $this->parseFile();
        $configuration[$name] = $value;
        $this->dumpFile($configuration);
    }

    /**
     * Returns a configuration.
     *
     * @return mixed The configuration value
     */
    public function getConfiguration(string $name)
    {
        $this->validateName($name, true);
        $configuration = $this->parseFile();

        return $configuration[$name] ?? null;
    }

    /**
     * Deletes a configuration.
     */
    public function delConfiguration(string $name): void
    {
        $this->validateName($name, false);
        $configuration = $this->parseFile();
        if (isset($configuration[$name])) {
            unset($configuration[$name]);
            $this->dumpFile($configuration);
        }
    }

    /**
     * Returns configuration in html format.
     */
    public function getConfigurationForHtml(string $name): string
    {
        $config = $this->getConfiguration($name);

        return $this->formatValue($config);
    }

    /**
     * Formats a value for html (recursive array safe).
     *
     * @param mixed $value
     */
    protected function formatValue($value): string
    {
        if (null === $value) {
            return '<em>null</em>';
        }

        $html = '';

        foreach ($value as $key => $val) {
            $html .= '<li><strong>' . htmlspecialchars((string)$key, ENT_QUOTES) . ':</strong>';
            if (is_array($val)) {
                $html .= $this->formatValue($val) . "</li>\n";
            } else {
                $val = !empty($val) ? htmlspecialchars((string)$val, ENT_QUOTES) : '<em>null</em>';
                $html .= ' ' . $val . "</li>\n";
            }
        }

        return "<ul>\n${html}</ul>\n";
    }

    /**
     * Parses a Yaml file and return a configuration array.
     */
    protected function parseFile(): array
    {
        if (!$this->fs->exists($this->fullPath)) {
            return [];
        }

        return Yaml::parse(file_get_contents($this->fullPath));
    }

    /**
     * Dump configuration into dynamic configuration file.
     */
    protected function dumpFile(array $configuration = []): void
    {
        $yaml = Yaml::dump($configuration);
        $this->fs->dumpFile($this->fullPath, $yaml);
    }

    /**
     * Validates that the configuration / parameter name is correct.
     *
     * @throws InvalidArgumentException Thrown if the configuration / parameter is invalid
     */
    protected function validateName(string $name, bool $isParameter): void
    {
        if (!is_string($name) || (!$isParameter && 'parameters' === $name) || mb_strlen($name) <= 0) {
            if ($isParameter) {
                throw new InvalidArgumentException('The parameter name must be a string');
            }
            throw new InvalidArgumentException('The configuration name must not be "parameters" and must be a string');
        }
    }
}
