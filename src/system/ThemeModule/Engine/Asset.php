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
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\AbstractBundle;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

/**
 * Class Asset
 *
 * This class locates assets accounting for possible overrides in app/Resources/$bundleName or in the
 * active theme. It is foremost used by the zasset() Twig template plugin, but can be utilized as a standalone
 * service as well. All asset types (js, css, images) will work.
 *
 * Asset paths must begin with `@` in order to be processed (and possibly overridden) by this class.
 * Assets that do not contain `@` are passed through to the standard symfony asset management.
 *
 * Overrides are in this order:
 *  1) public/overrides/$bundleName/public/*
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
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        Packages $assetPackages,
        RouterInterface $router
    ) {
        $this->kernel = $kernel;
        $this->assetPackages = $assetPackages;
        $this->router = $router;
    }

    /**
     * Returns path for asset.
     */
    public function resolve(string $path): string
    {
        $projectDir = $this->kernel->getProjectDir();
        $basePath = $this->router->getContext()->getBaseUrl();
        $httpRootDir = str_replace($basePath, '', $projectDir);

        // return immediately for straight asset paths
        // doesn't check if file exists
        if ('@' !== $path[0]) {
            if (0 === mb_strpos($path, '/')) {
                $path = mb_substr($path, 1);
            }
            $publicPath = $this->assetPackages->getUrl($path);
            // TODO remove temporary hack
            if ('/src' === mb_substr($publicPath, 0, 4)) {
                $publicPath = mb_substr($publicPath, 4);
            }
            if (false !== realpath($httpRootDir . $publicPath)) {
                return $publicPath;
            }
        }

        [$bundleName, $originalPath] = explode(':', $path);

        // try to locate asset in public override path
        $overridePath = $projectDir . '/public/overrides/' . mb_substr($bundleName, 1) . '/' . $originalPath;
        if (false !== $fullPath = realpath($overridePath)) {
            $publicPath = $this->assetPackages->getUrl($overridePath);
            // TODO remove temporary hack
            if ('/src' === mb_substr($publicPath, 0, 4)) {
                $publicPath = mb_substr($publicPath, 4);
            }
            if (false !== realpath($httpRootDir . $publicPath)) {
                return $publicPath;
            }
        }

        // try to locate asset in it's normal public directory
        $path = $this->mapZikulaAssetPath($bundleName, $originalPath);
        if (false === $fullPath = realpath($overridePath)) {
            $publicPath = $this->assetPackages->getUrl($path);
            // TODO remove temporary hack
            if ('/src' === mb_substr($publicPath, 0, 4)) {
                $publicPath = mb_substr($publicPath, 4);
            }
            if (false !== realpath($httpRootDir . $publicPath)) {
                return $publicPath;
            }
        }

        // try to locate asset in the bundle directory
        $fullPath = $this->kernel->locateResource($bundleName . '/Resources/public/' . $originalPath);
        $resultPath = false !== mb_strpos($fullPath, $projectDir) ? str_replace($projectDir, '', $fullPath) : $fullPath;
        $resultPath = $basePath . str_replace(DIRECTORY_SEPARATOR, '/', $resultPath);
        // TODO remove temporary hack
        if ('/src/src' === mb_substr($resultPath, 0, 8)) {
            $resultPath = mb_substr($resultPath, 4);
        }

        return $this->assetPackages->getUrl($resultPath, 'zikula_default');
    }

    /**
     * Maps zasset path argument
     * e.g. "@AcmeBundle:css/foo.css" to `AcmeBundle/Resources/public/css/foo.css`
     */
    private function mapZikulaAssetPath(?string $bundleName, ?string $path): string
    {
        if (!isset($bundleName) || !isset($path)) {
            throw new InvalidArgumentException('No bundle name resolved, must be like "@AcmeBundle:css/foo.css"');
        }
        $bundle = $this->kernel->getBundle(mb_substr($bundleName, 1));
        if ($bundle instanceof Bundle) {
            $path = '/' . $path;
            if ($bundle instanceof AbstractBundle) {
                $path = $bundle->getRelativeAssetPath() . $path;
            } else {
                $path = mb_strtolower('Bundles/' . mb_substr($bundle->getName(), 0, -mb_strlen('Bundle'))) . $path;
            }
        }

        return $path;
    }
}
