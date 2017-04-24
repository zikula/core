<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use Symfony\Component\Asset\Packages;
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

    /**
     * Asset constructor.
     * @param ZikulaHttpKernelInterface $kernel
     * @param Packages $assetPackages
     */
    public function __construct(ZikulaHttpKernelInterface $kernel, Packages $assetPackages)
    {
        $this->kernel = $kernel;
        $this->assetPackages = $assetPackages;
    }

    /**
     * Get the path to the site root.
     * @return string
     */
    public function getSiteRoot()
    {
        return realpath($this->kernel->getRootDir() . "/../");
    }

    /**
     * Returns path for asset.
     *
     * @param string $path
     * @return string
     */
    public function resolve($path)
    {
        // for straight asset paths
        if ('@' !== $path[0]) {
            return $this->assetPackages->getUrl($path);
        }

        // Maps to AcmeBundle/Resources/public/$assetPath
        // @AcmeBundle:css/foo.css
        // @AcmeBundle:jss/foo.js
        // @AcmeBundle:images/foo.png
        $parts = explode(':', $path);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('No bundle name resolved, must be like "@AcmeBundle:css/foo.css"');
        }

        // if file exists in /web, then use it first
        $bundle = $this->kernel->getBundle(substr($parts[0], 1));
        if ($bundle instanceof AbstractBundle) {
            $relativeAssetPath = $bundle->getRelativeAssetPath() . '/' . $parts[1];
            $webPath = $this->assetPackages->getUrl($relativeAssetPath);
            $filePath = realpath($this->kernel->getRootDir() . '/../../../' . $webPath);
            if (is_file($filePath)) {
                return $webPath;
            }
        }

        $fullPath = $this->kernel->locateResource($parts[0] . '/Resources/public/' . $parts[1], 'app/Resources', true);
        $root = $this->getSiteRoot();
        $path = (false !== strpos($fullPath, $root)) ? substr($fullPath, strlen($root) + 1) : $fullPath;
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        return $this->assetPackages->getUrl($path, 'zikula_default');
    }
}
