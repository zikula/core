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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheClearer
{
    private LoggerInterface $logger;

    private bool $installed;

    private Filesystem $fileSystem;

    private array $cacheTypes = [];

    private array $cachesToClear;

    public function __construct(
        LoggerInterface $zikulaLogger,
        #[Autowire(service: 'cache_warmer')]
        private readonly CacheWarmerInterface $warmer,
        #[Autowire(service: 'fos_js_routing.extractor')]
        private readonly ExposedRoutesExtractorInterface $fosJsRoutesExtractor,
        #[Autowire('%kernel.cache_dir%')]
        private readonly string $cacheDir,
        #[Autowire('%kernel.container_class%')]
        private readonly string $kernelContainerClass,
        #[Autowire('%env(ZIKULA_INSTALLED)%')]
        string $installed
    ) {
        $this->logger = $zikulaLogger;
        $this->installed = '0.0.0' !== $installed;
        $this->fileSystem = new Filesystem();
        $this->cacheTypes = [];
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
        }
        if (function_exists('opcache_reset') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
            // This is a brute force clear of _all_ the cached files
            // because simply clearing the files in $this->cachesToClear isn't enough.
            // Perhaps if we could discern exactly which files to invalidate, we could
            // take a more precise approach with @opcache_invalidate($file, true).
            @opcache_reset();
            $this->logger->notice('OPCache cleared!');
        }
        // the cache must be warmed after deleting files
        $this->warmer->warmUp($this->cacheDir);
    }

    private function initialiseCacheTypeMap()
    {
        $fosJsRoutingFiles = [];
        if ($this->installed) {
            // avoid accessing FOS extractor before/during installation
            // because this requires request context
            $fosJsRoutingFiles[] = $this->fosJsRoutesExtractor->getCachePath();
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
