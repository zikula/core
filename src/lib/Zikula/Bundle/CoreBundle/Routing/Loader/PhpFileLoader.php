<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
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

namespace Zikula\Bundle\CoreBundle\Routing\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Loader\PhpFileLoader as BasePhpFileLoader;

/**
 * Customized PHP routing file loader exposing the container to the PHP file.
 */
class PhpFileLoader extends BasePhpFileLoader
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param FileLocatorInterface      $locator   A FileLocator instance
     */
    public function __construct(ContainerBuilder $container, FileLocatorInterface $locator)
    {
        $this->container = $container;

        parent::__construct($locator);
    }

    /**
     * {@inheritdoc}
     */
    public function load($file, $type = null)
    {
        // the loader variable is exposed to the included file below
        $container = $this->container;
        $loader = $this;

        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));

        $collection = include $path;
        $collection->addResource(new FileResource($path));

        return $collection;
    }
}