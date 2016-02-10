<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
     */
    public function __construct($cacheDir, $cachePrefix, $kernelContainerClass, $fosJsRoutesExtractor, $routingLocales)
    {
        $this->cacheDir = $cacheDir;
        $this->cachePrefix = $cachePrefix;
        $this->fs = new Filesystem();

        $cacheFolder = $cacheDir . DIRECTORY_SEPARATOR;

        $fosJsRoutingFiles = array();
        foreach ($routingLocales as $locale) {
            $fosJsRoutingFiles[] = $fosJsRoutesExtractor->getCachePath($locale);
        }

        $this->cacheTypes = array(
            "symfony.annotations" => array(
                "$cacheFolder/annotations"
            ),
            "symfony.routing.generator" => array(
                "$cacheFolder{$cachePrefix}UrlGenerator.php",
                "$cacheFolder{$cachePrefix}UrlGenerator.php.meta",
            ),
            "symfony.routing.matcher" => array(
                "$cacheFolder{$cachePrefix}UrlMatcher.php",
                "$cacheFolder{$cachePrefix}UrlMatcher.php.meta"
            ),
            "symfony.routing.fosjs" => $fosJsRoutingFiles,
            "symfony.config" => array(
                "$cacheFolder$kernelContainerClass.php",
                "$cacheFolder$kernelContainerClass.php.meta",
                "$cacheFolder$kernelContainerClass.xml",
                "$cacheFolder{$kernelContainerClass}Compiler.log",
                "{$cacheFolder}classes.map"
            ),
            "twig" => [
                "$cacheFolder/twig"
            ]
        );
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
