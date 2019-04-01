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

use FilesystemIterator;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\Filesystem\Filesystem;

class CacheClearer
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @var array
     */
    private $cacheTypes;

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct(
        string $cacheDir,
        string $cachePrefix,
        string $kernelContainerClass,
        ExposedRoutesExtractorInterface $fosJsRoutesExtractor,
        array $routingLocales = []
    ) {
        $this->cacheDir = $cacheDir;
        $this->cachePrefix = $cachePrefix;
        $this->fs = new Filesystem();

        $fosJsRoutingFiles = [];
        foreach ($routingLocales as $locale) {
            $fosJsRoutingFiles[] = $fosJsRoutesExtractor->getCachePath($locale);
        }

        $this->initialiseCacheTypeMap($kernelContainerClass, $fosJsRoutingFiles);
    }

    private function initialiseCacheTypeMap(string $kernelContainerClass, array $fosJsRoutingFiles = [])
    {
        $cacheFolder = $this->cacheDir . DIRECTORY_SEPARATOR;

        $this->cacheTypes = [
            'symfony.annotations' => [
                $cacheFolder . 'annotations'
            ],
            'symfony.routing.generator' => [
                $cacheFolder . $this->cachePrefix . 'UrlGenerator.php',
                $cacheFolder . $this->cachePrefix . 'UrlGenerator.php.meta'
            ],
            'symfony.routing.matcher' => [
                $cacheFolder . $this->cachePrefix . 'UrlMatcher.php',
                $cacheFolder . $this->cachePrefix . 'UrlMatcher.php.meta'
            ],
            'symfony.routing.fosjs' => $fosJsRoutingFiles,
            'symfony.config' => [
                $cacheFolder . $kernelContainerClass . '.php',
                $cacheFolder . $kernelContainerClass . '.php.meta',
                $cacheFolder . $kernelContainerClass . '.xml',
                $cacheFolder . $kernelContainerClass . 'Compiler.log',
                $cacheFolder . 'classes.map'
            ],
            'symfony.translations' => [
                $cacheFolder . '/translations'
            ],
            'twig' => [
                $cacheFolder . 'twig'
            ],
            'purifier' => [
                $cacheFolder . 'purifier'
            ],
            'assets' => [
                $cacheFolder . 'assets'
            ]
        ];
    }

    public function clear(string $type): void
    {
        foreach ($this->cacheTypes as $cacheType => $files) {
            if (0 !== mb_strpos($cacheType, $type)) {
                continue;
            }
            foreach ($files as $file) {
                if (is_dir($file)) {
                    // Do not delete the folder itself, but all files in it.
                    // Otherwise Symfony somehow can't create the folder anymore.
                    $file = new FilesystemIterator($file);
                }
                // This silently ignores non existing files.
                $this->fs->remove($file);
            }
        }
    }
}
