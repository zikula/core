<?php

namespace Zikula\Core\Theme;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
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

        $array = array();
        $array[] = array(
            'asset_path' => $path2 = $this->package->getUrl(preg_replace('#bundles/([\w\d_-]+)/(.*)$#', 'bundles/custom/$2', $path)),
            'full_path' => $this->package->getDocumentRoot().'/'.$this->package->getScriptPath().'/'.$path2,
        );

        $themeName = strtolower($this->package->getThemeName());
        $array[] = array(
            'asset_path' => $path2 = $this->package->getUrl(preg_replace('#bundles/([\w\d_-]+)/(.*)$#', 'bundles/'.$themeName.'/$2', $path)),
            'full_path' => $this->package->getDocumentRoot().'/'.$this->package->getScriptPath().'/'.$path2,
        );

        $array[] = array(
            'asset_path' => $this->package->getUrl($path),
            'full_path' => $this->package->getDocumentRoot().'/'.$this->package->getScriptPath().'/'.$path,
        );

        return $array;
    }

    private function getAssetPath($bundleName, $assetPath)
    {
        $bundle = $this->kernel->getBundle($bundleName);
        $assetDir = 'bundles/';
        $targetDir = $assetDir.preg_replace('/bundle$/', '', strtolower($bundle->getName()));

        return array(
            'asset_path' => $this->package->getUrl($targetDir.'/'.$assetPath),
            'full_path' => $this->package->getDocumentRoot().'/'.$this->package->getScriptPath().'/'.$targetDir.'/'.$assetPath,
        );
    }

    private function getSearchPath(array $parameters)
    {
        $paths = array();

        // custom
        // bundles/custom/$assetPath
        $paths[] = $this->getAssetPath('CustomBundle', $parameters['asset_path']);

        // theme
        // bundles/themename/$assetPath
        $themeName = $this->package->getThemeName();
        if (false === empty($themeName) && $parameters['bundle_name'] !== $themeName) {
            $paths[] = $this->getAssetPath($this->package->getThemeName(), $parameters['asset_path']);
        }

        // web
        // bundles/foo/$assetPath
        $paths[] = $this->getAssetPath($parameters['bundle_name'], $parameters['asset_path']);
        /* // todo
                // bundle (but only if it's visible from the webroot)
                // FooBundle/Resources/public/$assetPath
                $bundle = $this->kernel->getBundle($parameters['bundle_name']);
                $path = 'Resources/public/'.$parameters['asset_path'];
                $paths[] = array(
                    // todo - build URL from full path minus other stuff, and only then if in DOCUMENT_ROOT (since it's only available in that location)
                    'asset_path' => $this->package->getUrl($bundle->getName().'/'.$path),
                    'full_path' => str_replace('\\', '/', $bundle->getPath().'/'.$path),
                );
        */
        return $paths;
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