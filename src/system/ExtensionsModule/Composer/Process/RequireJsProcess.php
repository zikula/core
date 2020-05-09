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

namespace Zikula\ExtensionsModule\Composer\Process;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Assetic\Filter\FilterInterface;
use ComponentInstaller\Process\Process;
use Composer\Json\JsonFile;
use ReflectionClass;

/**
 * Builds the require.js configuration.
 */
class RequireJsProcess extends Process
{
    /**
     * The base URL for the require.js configuration.
     */
    protected $baseUrl = 'components';

    public function init()
    {
        $output = parent::init();
        if ($this->config->has('component-baseurl')) {
            $this->baseUrl = $this->config->get('component-baseurl');
        }

        return $output;
    }

    public function process()
    {
        // Construct the require.js and stick it in the destination.
        $json = $this->requireJson($this->packages);
        $requireConfig = $this->requireJs($json);
        $vendorPath = $this->config->get('vendor-dir') . '/robloach/component-installer/src/ComponentInstaller';

        // Attempt to write the require.config.js file.
        $destination = $this->componentDir . '/require.config.js';
        $this->fs->ensureDirectoryExists(dirname($destination));
        if (false === file_put_contents($destination, $requireConfig)) {
            $this->io->write('<error>Error writing require.config.js</error>');

            return false;
        }

        // Read in require.js to prepare the final require.js.
        if (!file_exists($vendorPath . '/Resources/require.js')) {
            $this->io->write('<error>Error reading in require.js</error>');

            return false;
        }

        $assets = $this->newAssetCollection();
        $assets->add(new FileAsset($vendorPath . '/Resources/require.js'));
        $assets->add(new StringAsset($requireConfig));

        // Append the config to the require.js and write it.
        if (false === file_put_contents($this->componentDir . '/require.js', $assets->dump())) {
            $this->io->write('<error>Error writing require.js to the components directory</error>');

            return false;
        }

        return true;
    }

    /**
     * Creates a require.js configuration (JSON array) based on an array of packages from the composer.lock file.
     */
    public function requireJson(array $packages = []): array
    {
        $json = [];

        // Construct the packages configuration.
        foreach ($packages as $package) {
            // Retrieve information from the extra options.
            $extra = $package['extra'] ?? [];
            $options = $extra['component'] ?? [];

            // Construct the base details.
            $name = $this->getComponentName($package['name'], $extra);
            $component = [
                'name' => $name,
            ];

            // Build the "main" directive.
            $scripts = $options['scripts'] ?? [];
            if (!empty($scripts)) {
                // Put all scripts into a build.js file.
                $result = $this->aggregateScripts($package, $scripts, $name . DIRECTORY_SEPARATOR . $name . '-built.js');
                if (false !== $result) {
                    // If the aggregation was successful, add the script to the
                    // packages array.
                    $component['main'] = $name . '-built.js';

                    // Add the component to the packages array.
                    $json['packages'][] = $component;
                }
            }

            // Add the shim definition for the package.
            $shim = $options['shim'] ?? [];
            if (!empty($shim)) {
                $json['shim'][$name] = $shim;
            }

            // Add the config definition for the package.
            $packageConfig = $options['config'] ?? [];
            if (!empty($packageConfig)) {
                $json['config'][$name] = $packageConfig;
            }
        }

        // Provide the baseUrl.
        $json['baseUrl'] = $this->baseUrl;

        // Merge in configuration options from the root.
        if ($this->config->has('component')) {
            $config = $this->config->get('component');
            if (isset($config) && is_array($config)) {
                // Use a recursive, distict array merge.
                $json = $this->arrayMergeRecursiveDistinct($json, $config);
            }
        }

        return $json;
    }

    /**
     * Concatenate all scripts together into one destination file.
     */
    public function aggregateScripts(array $package, array $scripts, string $file): bool
    {
        $assets = $this->newAssetCollection();

        foreach ($scripts as $script) {
            // Collect each candidate from a glob file search.
            $path = $this->getVendorDir($package) . DIRECTORY_SEPARATOR . $script;
            $matches = $this->fs->recursiveGlobFiles($path);
            foreach ($matches as $match) {
                $assets->add(new FileAsset($match));
            }
        }
        $js = $assets->dump();

        // Write the file if there are any JavaScript assets.
        if (!empty($js)) {
            $destination = $this->componentDir . DIRECTORY_SEPARATOR . $file;
            $this->fs->ensureDirectoryExists(dirname($destination));

            return file_put_contents($destination, $js) ? true : false;
        }

        return false;
    }

    /**
     * Constructs the require.js file from the provided require.js JSON array.
     */
    public function requireJs(array $json = []): string
    {
        // Encode the array to a JSON array.
        $js = JsonFile::encode($json);

        // Construct the JavaScript output.
        $output = <<<EOT
var components = ${js};
components.baseUrl = Zikula.Config.baseURL + "web";                
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}
EOT;

        return $output;
    }

    /**
     * Merges two arrays without changing string array keys. Appends to array if keys are numeric.
     *
     * @see array_merge()
     * @see array_merge_recursive()
     */
    protected function arrayMergeRecursiveDistinct(array &$array1, array &$array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_numeric($key)) {
                $merged[] = $value;
            } elseif (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    protected function newAssetCollection(): AssetCollection
    {
        // Aggregate all the assets into one file.
        $assets = new AssetCollection();
        if ($this->config->has('component-scriptFilters')) {
            $filters = $this->config->get('component-scriptFilters');
            if (isset($filters) && is_array($filters)) {
                foreach ($filters as $filter => $filterParams) {
                    $reflection = new ReflectionClass($filter);
                    /** @var FilterInterface $filter */
                    $filter = $reflection->newInstanceArgs($filterParams);
                    $assets->ensureFilter($filter);
                }
            }
        }

        return $assets;
    }
}
