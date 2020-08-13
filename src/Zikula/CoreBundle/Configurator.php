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
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * This class manages configuration, validation and write-to-file for a select
 * subgroup of bundle configs (see $configurablePackages).
 *
 * To use, one must `loadPackages` by name. `Set` configuration as needed.
 * Then `write` the package config to the desired location. When the config
 * is loaded, the existing (if any) config settings are loaded in addition
 * to any default settings.
 *
 * The class defaults to write only the minimal required config (removing default
 * values) into the main `config/packages` (no env) directory.
 */
class Configurator
{
    /** @var string */
    private $configDir;
    /** @var Filesystem */
    private $fs;
    /** @var array */
    private $processedConfigurations = [];
    /** @var array */
    private $defaultConfigurations = [];
    /** @var string[] */
    private $configurablePackages = [
        'core' => 'Zikula\Bundle\CoreBundle\DependencyInjection\Configuration',
        'zikula_security_center' => 'Zikula\SecurityCenterModule\DependencyInjection\Configuration',
        'zikula_theme' => 'Zikula\ThemeModule\DependencyInjection\Configuration',
        'zikula_routes' => 'Zikula\RoutesModule\DependencyInjection\Configuration'
//        'bazinga_js_translation' => 'Bazinga\Bundle\JsTranslationBundle\DependencyInjection\Configuration',
//        'php_translation' => 'Translation\Bundle\DependencyInjection\Configuration',
//        'translation' => 'Symfony\Bundle\FrameworkBundle\DependencyInjection\Configuration',
    ];

    public function __construct(string $projectDir)
    {
        $this->configDir = $projectDir . '/config';
        $this->fs = new Filesystem();
    }

    public function loadPackages($packages, string $env = 'prod'): void
    {
        if (!is_array($packages)) {
            $packages = (array) $packages;
        }
        foreach ($packages as $package) {
            if (!isset($this->configurablePackages[$package])) {
                throw new \InvalidArgumentException(sprintf('Package %s is not available for configuration. Please configure manually.', $package));
            }
            $configs = [];
            foreach ($this->getPaths($env) as $path) {
                if (file_exists($fullPath = $path . $package . '.yaml') && false !== $contents = file_get_contents($fullPath)) {
                    $configs[] = Yaml::parse($contents)[$package];
                }
            }
            $configuration = new $this->configurablePackages[$package]();
            $this->processedConfigurations[$package] = $this->process(
                $configuration,
                $configs
            );
            $this->defaultConfigurations[$package] = $this->process($configuration);
        }
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function validatePackage(string $package): array
    {
        $config = $this->getAll($package);
        /** @var ConfigurationInterface $configuration */
        return $this->process(
            $configuration = new $this->configurablePackages[$package](),
            // $package could be wrong key here - maybe need to get configuration root
            [$package => $config]
        );
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function process(ConfigurationInterface $configuration, array $config = []): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(
            $configuration,
            $config
        );
    }

    /**
     * @throws IOException if the file(s) cannot be written to
     */
    public function write(bool $min = true, string $env = ''): void
    {
        foreach (array_keys($this->processedConfigurations) as $package) {
            $this->validatePackage($package);
            $this->writePackage($package, $min, $env);
        }
    }

    /**
     * @throws IOException if the file cannot be written to
     */
    public function writePackage(string $package, bool $min = true, string $env = ''): void
    {
        $config = $min ? $this->arrayDiffAssocRecursive($this->processedConfigurations[$package], $this->getDefaults($package)) : $this->processedConfigurations[$package];
        $flags = Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE;
        // $package could be wrong key here - maybe need to get configuration root
        $input = [$package => $config];
        $yaml = Yaml::dump($input, 4, 4, $flags);
        $basePath = $this->configDir . '/packages/' . (!empty($env) ? $env . '/' : '');
        $path = $basePath . $package . '.yaml';
        $this->fs->dumpFile($path, $yaml);
    }

    public function set(string $package, string $key, $value): void
    {
        if (!isset($this->processedConfigurations[$package][$key])) {
            throw new InvalidArgumentException(sprintf('Cannot set %s in the %s package because it is not configurable', $key, $package));
        }

        $this->processedConfigurations[$package][$key] = $value;
    }

    public function get(string $package, string $key)
    {
        if (!isset($this->processedConfigurations[$package][$key])) {
            throw new InvalidArgumentException(sprintf('The %s is not set in the %s package', $key, $package));
        }

        return $this->processedConfigurations[$package][$key];
    }

    public function getAll(string $package): array
    {
        if (!isset($this->processedConfigurations[$package])) {
            throw new InvalidArgumentException(sprintf('The %s package is not set', $package));
        }

        return $this->processedConfigurations[$package];
    }

    public function getDefaults(string $package): array
    {
        if (!isset($this->defaultConfigurations[$package])) {
            throw new InvalidArgumentException(sprintf('The %s package is not set', $package));
        }

        return $this->defaultConfigurations[$package];
    }

    private function getPaths(string $env = 'prod'): array
    {
        return [
            0 => $this->configDir . '/packages/',
            1 => $this->configDir . '/packages/' . $env,
        ];
    }

    /**
     * @author Giosh 2013-03-15
     * @see https://www.php.net/manual/en/function.array-diff-assoc.php#111675
     */
    public function arrayDiffAssocRecursive(array $array1, array $array2): array
    {
        $difference=[];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $newDiff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                    if (!empty($newDiff)) {
                        $difference[$key] = $newDiff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }
}
