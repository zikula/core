<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

trigger_deprecation('zikula/core-bundle', '3.1', 'The "%s" class is deprecated. Use "%s" instead.', YamlDumper::class, Configurator::class);

/**
 * @deprecated
 * @internal
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

    public function __construct(string $configDir, string $filePath = 'services_custom.yaml')
    {
        $this->fullPath = $configDir . DIRECTORY_SEPARATOR . $filePath;
        $this->fs = new Filesystem();
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
     * Parses a Yaml file and return a configuration array.
     */
    protected function parseFile(?string $path = null): array
    {
        $path = $path ?? $this->fullPath;
        if (!$this->fs->exists($path)) {
            return [];
        }

        return Yaml::parse(file_get_contents($path));
    }

    /**
     * Dump configuration into dynamic configuration file.
     */
    protected function dumpFile(array $configuration = []): void
    {
        $yaml = Yaml::dump($configuration);
        $this->fs->dumpFile($this->fullPath, $yaml);
    }

    public function deleteFile(): void
    {
        $this->fs->remove($this->fullPath);
    }

    /**
     * Validates that the name is correct.
     *
     * @throws InvalidArgumentException Thrown if the name is invalid
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
