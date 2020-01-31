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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class CacheClearer
{
    /**
     * @var ContainerInterface
     */
    private $container;

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

    public function __construct(
        ContainerInterface $container,
        string $cacheDir,
        string $kernelContainerClass,
        bool $installed,
        array $routingLocales = []
    ) {
        $this->container = $container;
        $this->cacheDir = $cacheDir;
        $this->kernelContainerClass = $kernelContainerClass;
        $this->installed = $installed;
        $this->routingLocales = $routingLocales;
        $this->fileSystem = new Filesystem();
    }

    public function clear(string $type): void
    {
        if (!count($this->cacheTypes)) {
            $this->initialiseCacheTypeMap();
        }

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
        }
    }

    private function initialiseCacheTypeMap()
    {
        $fosJsRoutingFiles = [];
        if ($this->installed) {
            // avoid accessing FOS extractor before/during installation
            // because this requires request context

            /** @var ExposedRoutesExtractorInterface */
            $fosJsRoutesExtractor = $this->container->get('fos_js_routing.extractor');
            foreach ($this->routingLocales as $locale) {
                $fosJsRoutingFiles[] = $fosJsRoutesExtractor->getCachePath($locale);
            }
        }

        $cacheFolder = $this->cacheDir . DIRECTORY_SEPARATOR;

        $this->cacheTypes = [
            'symfony.annotations' => [
                $cacheFolder . 'annotations'
            ],
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
                $cacheFolder . $this->kernelContainerClass . '.php',
                $cacheFolder . $this->kernelContainerClass . '.php.meta',
                $cacheFolder . $this->kernelContainerClass . '.xml',
                $cacheFolder . $this->kernelContainerClass . 'Compiler.log',
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
}
