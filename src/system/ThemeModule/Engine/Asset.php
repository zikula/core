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
 *  1) app/Resources/$bundleName/public/* @todo
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
        // return immediately for straight asset paths
        // doesn't check if file exists
        if ('@' !== $path[0]) {
            if (0 === mb_strpos($path, '/')) {
                $path = mb_substr($path, 1);
            }

            return $this->assetPackages->getUrl($path);
        }
        [$bundleName, $originalPath] = explode(':', $path);
        $path = $this->mapZikulaAssetPath($bundleName, $originalPath);

        // if file exists in /web, then use it first
        $httpRootDir = str_replace($this->router->getContext()->getBaseUrl(), '', $this->kernel->getProjectDir());
        $webPath = $this->assetPackages->getUrl($path);
        if (false !== realpath($httpRootDir . $webPath)) {
            return $webPath;
        }

        // try to locate the asset in the bundle directory or global override
        $projectDir = $this->kernel->getProjectDir();
        $fullPath = $this->kernel->locateResource($bundleName . '/Resources/public/' . $originalPath);
        if (false === realpath($fullPath)) {
            // try to find the asset in the global override path.  @todo update for Symfony 5 structure
            $fullPath = $this->kernel->locateResource('app/Resources/public/' . $originalPath);
        }
        $resultPath = false !== mb_strpos($fullPath, $projectDir) ? str_replace($projectDir, '', $fullPath) : $fullPath;
        $resultPath = str_replace(DIRECTORY_SEPARATOR, '/', $resultPath);

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
