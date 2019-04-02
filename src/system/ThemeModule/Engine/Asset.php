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

namespace Zikula\ThemeModule\Engine;

use InvalidArgumentException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\AbstractBundle;

/**
 * Class Asset
 *
 * This class locates assets accounting for possible overrides in app/Resources/$bundleName or in the
 * active theme. It is foremost used by the zasset() Twig template plugin, but can be utilized as a standalone
 * service as well. All asset types (js, css, images) will work.
 *
 * Asset paths must begin with `@` in order to be processed (and possibly overridden) by this class.
 * Assets that do not contain `@` are passed through to the standard symfony asset management.
 * Assets from the `/web` directory cannot be overridden.
 *
 * Overrides are in this order:
 *  1) app/Resources/$bundleName/public/*
 *  2) $theme/Resources/$bundleName/public/*
 *  3) $bundleName/Resources/public/*
 */
class Asset
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var Packages
     */
    private $assetPackages;

    public function __construct(ZikulaHttpKernelInterface $kernel, Packages $assetPackages)
    {
        $this->kernel = $kernel;
        $this->assetPackages = $assetPackages;
    }

    /**
     * Get the path to the site root.
     */
    public function getSiteRoot(): string
    {
        return dirname($this->kernel->getProjectDir());
    }

    /**
     * Returns path for asset.
     */
    public function resolve(string $path): string
    {
        // for straight asset paths
        if ('@' !== $path[0]) {
            if (0 === strpos($path, '/')) {
                $path = mb_substr($path, 1);
            }

            return $this->assetPackages->getUrl($path);
        }

        // Maps to AcmeBundle/Resources/public/$assetPath
        // @AcmeBundle:css/foo.css
        // @AcmeBundle:jss/foo.js
        // @AcmeBundle:images/foo.png
        $parts = explode(':', $path);
        if (2 !== count($parts)) {
            throw new InvalidArgumentException('No bundle name resolved, must be like "@AcmeBundle:css/foo.css"');
        }

        // if file exists in /web, then use it first
        $bundle = $this->kernel->getBundle(mb_substr($parts[0], 1));
        if ($bundle instanceof Bundle) {
            $relativeAssetPath = '/' . $parts[1];
            if ($bundle instanceof AbstractBundle) {
                $relativeAssetPath = $bundle->getRelativeAssetPath() . $relativeAssetPath;
            } else {
                $relativeAssetPath = mb_strtolower('Bundles/' . mb_substr($bundle->getName(), 0, -mb_strlen('Bundle'))) . $relativeAssetPath;
            }

            $webPath = $this->assetPackages->getUrl($relativeAssetPath);
            $filePath = $this->kernel->getProjectDir() . '/..' . $webPath;
            if (is_file($filePath)) {
                return $webPath;
            }
        }

        $fullPath = $this->kernel->locateResource($parts[0] . '/Resources/public/' . $parts[1], 'app/Resources');
        $root = $this->getSiteRoot();

        $resultPath = false !== mb_strpos($fullPath, $root) ? str_replace($root, '', $fullPath) : $fullPath;
        $resultPath = str_replace(DIRECTORY_SEPARATOR, '/', $resultPath);

        return $this->assetPackages->getUrl($resultPath, 'zikula_default');
    }
}
