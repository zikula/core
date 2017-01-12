<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Bundle;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Scanner
{
    private $jsons = [];

    /**
     * Scans and loads composer.json files.
     *
     * @param array $paths
     * @param int $depth
     * @param Finder $finder
     */
    public function scan(array $paths, $depth = 3, Finder $finder = null)
    {
        $paths = (array)$paths;
        $finder = null === $finder ? new Finder() : $finder;
        $finder->files()
            ->in($paths)
            ->notPath('docs')
            ->notPath('vendor')
            ->notPath('Resources')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->depth('<' . $depth)
            ->name('composer.json');

        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $json = $this->decode($file->getRealPath());
            if (false !== $json) {
                $this->jsons[$json['name']] = $json;
            }
        }
    }

    /**
     * Decodes a json string.
     *
     * @param string $file Path to json file
     *
     * @return bool|mixed
     */
    public function decode($file)
    {
        $base = str_replace('\\', '/', dirname($file));
        $zkRoot = realpath(dirname(__FILE__) . '/../../../../../');
        $base = substr($base, strlen($zkRoot) + 1);

        $json = json_decode($this->getFileContents($file), true);
        if (\JSON_ERROR_NONE === json_last_error() && true === $this->validateBasic($json)) {
            // add base-path for future use
            $json['extra']['zikula']['base-path'] = $base;

            // calculate PSR-0/4 autoloading path for this namespace
            $class = $json['extra']['zikula']['class'];
            $ns = substr($class, 0, strrpos($class, '\\') + 1);
            if (false === isset($json['autoload']['psr-0'][$ns]) &&
                false === isset($json['autoload']['psr-4'][$ns])
            ) {
                return false;
            }

            $nsShort = str_replace('\\', '/', substr($class, 0, strrpos($class, '\\')));
            if (isset($json['autoload']['psr-0'][$ns])) {
                $path = $json['extra']['zikula']['root-path'] = substr($base, 0, strpos($base, $nsShort) - 1);
                $json['autoload']['psr-0'][$ns] = $path;
            } elseif (isset($json['autoload']['psr-4'][$ns])) {
                $path = $json['extra']['zikula']['root-path'] = $base;
                $json['autoload']['psr-4'][$ns] = $path;
            }
            $json['extra']['zikula']['short-name'] = substr($class, strrpos($class, '\\') + 1, strlen($class));
            $json['extensionType'] = \ZikulaKernel::isCoreModule($json['extra']['zikula']['short-name']) ? MetaData::TYPE_SYSTEM : MetaData::TYPE_MODULE;

            return $json;
        }

        return false;
    }

    public function getFileContents($file)
    {
        return file_get_contents($file);
    }

    public function getModulesMetaData($indexByShortName = false)
    {
        return $this->getMetaData('zikula-module', $indexByShortName);
    }

    public function getThemesMetaData($indexByShortName = false)
    {
        return $this->getMetaData('zikula-theme', $indexByShortName);
    }

    // @todo remove for 2.0
    public function getPluginsMetaData($indexByShortName = false)
    {
        return $this->getMetaData('zikula-plugin', $indexByShortName);
    }

    // @todo this can probably be removed in favour of ComposerValidationHelper
    private function validateBasic($json)
    {
        if (!isset($json['type'])) {
            return false;
        }

        switch ($json['type']) {
            case 'zikula-module':
            case 'zikula-theme':
            // @todo remove for 2.0
            case 'zikula-plugin':
                break;
            default:
                return false;
        }

        // @todo remove psr-0 for 2.0
        if (!isset($json['autoload']['psr-0']) && !isset($json['autoload']['psr-4'])) {
            return false;
        }

        if (!isset($json['extra']['zikula']['class'])) {
            return false;
        }

        return true;
    }

    private function getMetaData($type, $indexByShortName)
    {
        $array = [];
        foreach ($this->jsons as $json) {
            if ($json['type'] === $type) {
                $indexField = $indexByShortName ? $json['extra']['zikula']['short-name'] : $json['name'];
                $array[$indexField] = new MetaData($json);
            }
        }

        return $array;
    }
}
