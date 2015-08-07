<?php

namespace Zikula\Core\Theme;

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Core\Theme\Asset\PackagePath;

class Asset
{
    private $kernel;
    private $package;
    private $webDir;

    public function __construct(KernelInterface $kernel, PackagePath $package, $webDir = 'web')
    {
        $this->kernel = $kernel;
        $this->package = $package;
        $this->webDir = $webDir;
    }

    /**
     * Returns path for asset.
     *
     * @param $path
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function resolve($path)
    {
        // for straight asset paths
        if ('@' !== $path[0]) {
            return $this->choose($this->resolvePath($path));
        }

        // Maps to AcmeBundle/Resources/public/$assetPath
        // @AcmeBundle:css/foo.css
        // @AcmeBundle:jss/foo.js
        // @AcmeBundle:images/foo.png
        $bundleName = null;
        $parts = explode(':', $path);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('No bundle name resolved, must be like "@AcmeBundle:css/foo.css"');
        }


        $bundleName = substr($parts[0], 1, strlen($parts[0]));
        $assetPath = $parts[1];

        $parameters = array(
            'bundle_name' => $bundleName,
            'asset_path' => $assetPath,
        );

        return $this->choose($this->getSearchPath($parameters));
    }

    public function resolvePath($path)
    {
        // just expect something already in assets folder
        // replace first part of assets folder /bundles/$name/* with /bundles/custom/*

        /*
         * @todo this does not yet check the correct paths - see `customizedAssetPath`
         */

        $array = array();
        // custom
        if (strpos($path, '/bundles')) {
            // @todo what about /modules and /themes ?
            $array[] = array(
                // @todo needs to convert /bundles/bundlename/css/... to /bundles/custom/css/bundlename/...
                'asset_path' => $path2 = $this->package->getUrl($this->webDir . '/' . preg_replace('#bundles/([\w\d_-]+)/(.*)$#', 'bundles/custom/$2', $path)),
                'full_path' => $this->package->getDocumentRoot() . $path2,
            );

            // theme
            $themeName = strtolower($this->package->getThemeName());
            $array[] = array(
                // @todo needs to convert /bundles/bundlename/css/... to /bundles/themename/css/bundlename/...
                'asset_path' => $path2 = $this->package->getUrl($this->webDir . '/' . preg_replace('#bundles/([\w\d_-]+)/(.*)$#', 'bundles/'.$themeName.'/$2', $path)),
                'full_path' => $this->package->getDocumentRoot() . $path2,
            );
        }

        // web
        // @todo look how the normal asset() plugin works and compare
        $array[] = array(
            'asset_path' => $path2 = $this->package->getUrl($this->webDir . '/' . $path),
            'full_path' => $this->package->getDocumentRoot() . $path2,
        );

        // @todo search bundle dir?

        return $array;
    }

    private function getAssetPath($bundleName, $assetPath)
    {
        $bundle = $this->kernel->getBundle($bundleName);
        $bundleType = method_exists($bundle, 'getNameType') ? strtolower($bundle->getNameType()) : 'bundle';

        $assetDir = "{$this->webDir}/{$bundleType}s/";
        $targetDir = $assetDir . str_replace($bundleType, '', strtolower($bundle->getName()));

        return array(
            'asset_path' => $this->package->getUrl($targetDir.'/'.$assetPath),
            'full_path' => $this->package->getDocumentRoot().'/'.$this->package->getScriptPath().'/'.$targetDir.'/'.$assetPath,
        );
    }

    private function getSearchPath(array $parameters)
    {
        $paths = array();

        // customized in customBundle
        // bundles/custom/$assetType/$bundleName/$assetPath
        $paths[] = $this->getAssetPath('CustomBundle', $this->customizedAssetPath($parameters['bundle_name'], $parameters['asset_path']));

        // customized in theme
        // themes/$themeName/$assetType/$bundleName/$assetPath
        $themeName = $this->package->getThemeName();
        if (false === empty($themeName) && $parameters['bundle_name'] !== $themeName) {
//            $assetPath = substr_replace($parameters['asset_path'], '/' . $parameters['bundle_name'], strpos($parameters['asset_path'], '/'), 0);
//            $paths[] = $this->getAssetPath($this->package->getThemeName(), $assetPath);
            $paths[] = $this->getAssetPath($this->package->getThemeName(), $this->customizedAssetPath($parameters['bundle_name'], $parameters['asset_path']));
        }

        // web
        // bundles/$bundleName/$assetPath
        $paths[] = $this->getAssetPath($parameters['bundle_name'], $parameters['asset_path']);

        // bundle
        // (modules|themes|system)/FooBundle/Resources/public/$assetPath
        $bundle = $this->kernel->getBundle($parameters['bundle_name']);
        // is it visible (within) from the webroot ?
        if (false !== $pathStart = strpos($bundle->getPath(), $this->package->getScriptPath())) {
            $path = 'Resources/public/' . $parameters['asset_path'];
            $pathStart += strlen($this->package->getScriptPath()) + 1;
            $fullPath = str_replace('\\', '/', $bundle->getPath() . '/' . $path);
            // remove the stuff after the script path...
            $paths[] = array(
                'asset_path' => $this->package->getUrl(substr($fullPath, $pathStart)),
                'full_path' => $fullPath
            );
        }

        return $paths;
    }

    /**
     * Convert to customized asset path by inserting bundleName
     * {assetType}/{BundleName}/{assetPath}.{assetType}
     * e.g. css/AcmeFooModule/path/to/asset.css
     *
     * @param $bundleName
     * @param $path
     * @return string
     */
    private function customizedAssetPath($bundleName, $path)
    {
        $parts = explode('/', $path);
        $assetType = array_shift($parts);
        array_unshift($parts, $assetType, $bundleName);

        return implode('/', $parts);
    }

    private function choose($paths)
    {
        foreach ($paths as $path) {
            if (true === is_readable($path['full_path'])) {
                return $path['asset_path'];
            }
        }

        return false;
    }
}
