<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Symfony\Component\Finder\Finder;

class Scanner
{
    private $jsons = array();

    /**
     * Scans and loads composer.json files.
     *
     * @param array  $paths
     * @param int    $depth
     * @param Finder $finder
     */
    public function scan(array $paths, $depth = 3, Finder $finder = null)
    {
        $paths = (array) $paths;
        $finder = null === $finder ? new Finder() : $finder;
        $finder->files()
            ->in($paths)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->depth('<'.$depth)
            ->name('composer.json');

        /** @var $f \SplFileInfo */
        foreach ($finder as $f) {
            $json = $this->decode($f->getRealPath());
            if (false !== $json) {
                $this->jsons[$json['name']] = $json;
            }
        }
    }

    /**
     * Decodes a json string.
     *
     * @param string $file Path to json file.
     *
     * @return bool|mixed
     */
    public function decode($file)
    {
        $base = str_replace('\\', '/', dirname($file));
        $json = json_decode($this->getFileContents($file), true);
        if (\JSON_ERROR_NONE === json_last_error() && true === $this->validateBasic($json)) {
            // add base-path for future use
            $json['extra']['zikula']['base-path'] = $base;

            // calculate PSR-0 autoloading path for this namespace
            $class = $json['extra']['zikula']['class'];
            $ns = substr($class, 0, strrpos($class, '\\') + 1);
            if (false === isset($json['autoload']['psr-0'][$ns])) {
                return false;
            }

            $nsShort = str_replace('\\', '/', substr($class, 0, strrpos($class, '\\')));
            $json['autoload']['psr-0'][$ns] = $json['extra']['zikula']['root-path'] = substr($base, 0, strpos($base, $nsShort) - 1);
            $json['extra']['zikula']['short-name'] = substr($class, strrpos($class, '\\') + 1, strlen($class));

            return $json;
        }

        return false;
    }

    public function getFileContents($file)
    {
        return file_get_contents($file);
    }

    public function getModulesMetaData()
    {
        return $this->getMetaData('zikula-module');
    }

    public function getThemesMetaData()
    {
        return $this->getMetaData('zikula-theme');
    }

    public function getPluginsMetaData()
    {
        return $this->getMetaData('zikula-plugin');
    }

    private function validateBasic($json)
    {
        if (!isset($json['type'])) {
            return false;
        }

        switch ($json['type']) {
            case 'zikula-module':
            case 'zikula-theme':
            case 'zikula-plugin':
                break;
            default;
                return false;
        }

        if (!isset($json['autoload']['psr-0'])) {
            return false;
        }

        if (!isset($json['extra']['zikula']['class'])) {
            return false;
        }

        return true;
    }

    private function getMetaData($type)
    {
        $array = array();
        foreach ($this->jsons as $json) {
            if ($json['type'] === $type && true) {
                $array[$json['name']] = new MetaData($json);
            }
        }

        return $array;
    }
}
