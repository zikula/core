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

use FilesystemIterator;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheClearer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExposedRoutesExtractorInterface
     */
    private $fosJsRoutesExtractor;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $kernelContainerClass;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var array
     */
    private $routingLocales = [];

    /**
     * @var array
     */
    private $cacheTypes = [];

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var CacheWarmerInterface
     */
    private $warmer;

    /**
     * @var array
     */
    private $cachesToClear;

    public function __construct(
        LoggerInterface $zikulaLogger,
        CacheWarmerInterface $warmer,
        ExposedRoutesExtractorInterface $fosJsRoutesExtractor,
        string $cacheDir,
        string $kernelContainerClass,
        string $installed,
        array $routingLocales = []
    ) {
        $this->logger = $zikulaLogger;
        $this->warmer = $warmer;
        $this->fosJsRoutesExtractor = $fosJsRoutesExtractor;
        $this->cacheDir = $cacheDir;
        $this->kernelContainerClass = $kernelContainerClass;
        $this->installed = '0.0.0' !== $installed;
        $this->routingLocales = $routingLocales;
        $this->fileSystem = new Filesystem();
        $this->cachesToClear = [];
    }

    /**
     * The cache is not cleared on demand.
     * Calling 'clear' will store caches to clear
     * This ensures no duplication and defers actual clearing
     * until kernel.terminate event
     * @see \Zikula\Bundle\CoreBundle\EventListener\CacheClearListener::doClearCache
     */
    public function clear(string $type): void
    {
        if (!isset($this->cachesToClear[$type])) {
            foreach ($this->cachesToClear as $value) {
                if (0 === mb_strpos($type, $value)) {
                    return;
                }
            }
            $this->cachesToClear[$type] = $type;
        }
    }

    /**
     * @internal
     * This is not a public api
     */
    public function doClear(): void
    {
        if (empty($this->cachesToClear)) {
            return;
        }

        if (!count($this->cacheTypes)) {
            $this->initialiseCacheTypeMap();
        }
        foreach ($this->cachesToClear as $type) {
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
                    $this->fileSystem->remove($file);
                }
                $this->logger->notice(sprintf('Cache cleared: %s', $cacheType));
            }
            // the cache must be warmed after deleting files
            $this->warmer->warmUp($this->cacheDir);
        }
    }

    private function initialiseCacheTypeMap()
    {
        $fosJsRoutingFiles = [];
        if ($this->installed) {
            // avoid accessing FOS extractor before/during installation
            // because this requires request context

            foreach ($this->routingLocales as $locale) {
                $fosJsRoutingFiles[] = $this->fosJsRoutesExtractor->getCachePath($locale);
            }
        }

        $cacheFolder = $this->cacheDir . DIRECTORY_SEPARATOR;

        $this->cacheTypes = [
            'symfony.routing.generator' => [
                $cacheFolder . 'url_generating_routes.php',
                $cacheFolder . 'url_generating_routes.php.meta'
            ],
            'symfony.routing.matcher' => [
                $cacheFolder . 'url_matching_routes.php',
                $cacheFolder . 'url_matching_routes.php.meta'
            ],
            'symfony.routing.fosjs' => $fosJsRoutingFiles,
            'symfony.config' => [
                // clearing the container class will force all other container files
                // to be rebuilt so there is no need to delete all of them
                // nor a need to delete the container directory
                $cacheFolder . $this->kernelContainerClass . '.php',
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
}
