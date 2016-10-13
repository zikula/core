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

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\Filesystem\Filesystem;

class CacheClearer
{
    private $cacheDir;

    private $cachePrefix;

    private $cacheTypes;

    private $fs;

    /**
     * @param string $cacheDir
     * @param string $cachePrefix
     * @param string $kernelContainerClass
     * @param ExposedRoutesExtractorInterface $fosJsRoutesExtractor
     * @param array $routingLocales
     * @param string $legacyCacheDir @deprecated
     */
    public function __construct($cacheDir, $cachePrefix, $kernelContainerClass, $fosJsRoutesExtractor, $routingLocales, $legacyCacheDir = null)
    {
        $this->cacheDir = $cacheDir;
        $this->cachePrefix = $cachePrefix;
        $this->fs = new Filesystem();

        $cacheFolder = $cacheDir . DIRECTORY_SEPARATOR;

        $fosJsRoutingFiles = [];
        foreach ($routingLocales as $locale) {
            $fosJsRoutingFiles[] = $fosJsRoutesExtractor->getCachePath($locale);
        }

        $this->cacheTypes = [
            'symfony.annotations' => [
                $cacheFolder . '/annotations'
            ],
            'symfony.routing.generator' => [
                $cacheFolder . $cachePrefix . 'UrlGenerator.php',
                $cacheFolder . $cachePrefix . 'UrlGenerator.php.meta'
            ],
            'symfony.routing.matcher' => [
                $cacheFolder . $cachePrefix . 'UrlMatcher.php',
                $cacheFolder . $cachePrefix . 'UrlMatcher.php.meta'
            ],
            'symfony.routing.fosjs' => $fosJsRoutingFiles,
            'symfony.config' => [
                $cacheFolder . $kernelContainerClass . '.php',
                $cacheFolder . $kernelContainerClass . '.php.meta',
                $cacheFolder . $kernelContainerClass . '.xml',
                $cacheFolder . $kernelContainerClass . 'Compiler.log',
                $cacheFolder . 'classes.map'
            ],
            'twig' => [
                $cacheFolder . 'twig'
            ],
            'purifier' => [
                $cacheFolder . 'purifier'
            ]
        ];
        if (isset($legacyCacheDir)) {
            $this->cacheTypes['legacy.cache.theme'] = [
                $legacyCacheDir . DIRECTORY_SEPARATOR . 'Theme_cache'
            ];
            $this->cacheTypes['legacy.cache.view'] = [
                $legacyCacheDir . DIRECTORY_SEPARATOR . 'view_cache'
            ];
            $this->cacheTypes['legacy.compiled.theme'] = [
                $legacyCacheDir . DIRECTORY_SEPARATOR . 'Theme_compiled'
            ];
            $this->cacheTypes['legacy.compiled.view'] = [
                $legacyCacheDir . DIRECTORY_SEPARATOR . 'view_compiled'
            ];
        }
    }

    public function clear($type)
    {
        foreach ($this->cacheTypes as $cacheType => $files) {
            if (substr($cacheType, 0, strlen($type)) === $type) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        // Do not delete the folder itself, but all files in it.
                        // Otherwise Symfony somehow can't create the folder anymore.
                        $file = new \FilesystemIterator($file);
                    }
                    // This silently ignores non existing files.
                    $this->fs->remove($file);
                }
            }
        }
    }
}
