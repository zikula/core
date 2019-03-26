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

namespace Zikula\Bundle\CoreBundle\Bundle;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Common\Translator\TranslatorInterface;

class Scanner
{
    private $jsons = [];

    private $invalid = [];

    /**
     * @var TranslatorInterface
     */
    private $translator;

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
            } else {
                $this->invalid[] = $file->getRelativePath();
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
        $zkRoot = realpath(__DIR__ . '/../../../../../');
        $base = mb_substr($base, mb_strlen($zkRoot) + 1);

        $json = json_decode($this->getFileContents($file), true);
        if (\JSON_ERROR_NONE === json_last_error()) {
            // add base-path for future use
            $json['extra']['zikula']['base-path'] = $base;

            // calculate PSR-4 autoloading path for this namespace
            $class = $json['extra']['zikula']['class'];
            $ns = mb_substr($class, 0, mb_strrpos($class, '\\') + 1);
            if (false === isset($json['autoload']['psr-4'][$ns])) {
                return false;
            }
            $path = $json['extra']['zikula']['root-path'] = $base;
            $json['autoload']['psr-4'][$ns] = $path;
            $json['extra']['zikula']['short-name'] = mb_substr($class, mb_strrpos($class, '\\') + 1, mb_strlen($class));
            $json['extensionType'] = ZikulaKernel::isCoreModule($json['extra']['zikula']['short-name']) ? MetaData::TYPE_SYSTEM : MetaData::TYPE_MODULE;

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

    private function getMetaData($type, $indexByShortName)
    {
        $array = [];
        foreach ($this->jsons as $json) {
            if ($json['type'] === $type) {
                $indexField = $indexByShortName ? $json['extra']['zikula']['short-name'] : $json['name'];
                $array[$indexField] = new MetaData($json);
                $array[$indexField]->setTranslator($this->translator);
            }
        }

        return $array;
    }

    public function getInvalid()
    {
        return $this->invalid;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
