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
use const JSON_ERROR_NONE;

class Scanner
{
    /**
     * @var array
     */
    private $jsons = [];

    /**
     * @var array
     */
    private $invalid = [];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Scans and loads composer.json files.
     */
    public function scan(array $paths = [], int $depth = 3, Finder $finder = null): void
    {
        $finder = $finder ?? new Finder();
        $finder->files()
            ->in($paths)
            ->notPath('docs')
            ->notPath('vendor')
            ->notPath('Resources')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->depth('<' . $depth)
            ->name('composer.json')
        ;

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
     * @return bool|mixed
     */
    public function decode(string $jsonFilePath)
    {
        $base = str_replace('\\', '/', dirname($jsonFilePath));
        $zkRoot = dirname(__DIR__, 5) . '/';
        $base = mb_substr($base, mb_strlen($zkRoot));

        $json = json_decode($this->getFileContents($jsonFilePath), true);
        if (JSON_ERROR_NONE === json_last_error()) {
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

    public function getFileContents(string $file): string
    {
        return file_get_contents($file);
    }

    public function getModulesMetaData(bool $indexByShortName = false): array
    {
        return $this->getMetaData('zikula-module', $indexByShortName);
    }

    public function getThemesMetaData(bool $indexByShortName = false): array
    {
        return $this->getMetaData('zikula-theme', $indexByShortName);
    }

    private function getMetaData(string $type, bool $indexByShortName): array
    {
        $array = [];
        foreach ($this->jsons as $json) {
            if ($json['type'] !== $type) {
                continue;
            }
            $indexField = $indexByShortName ? $json['extra']['zikula']['short-name'] : $json['name'];
            $array[$indexField] = new MetaData($json);
            $array[$indexField]->setTranslator($this->translator);
        }

        return $array;
    }

    public function getInvalid(): array
    {
        return $this->invalid;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
