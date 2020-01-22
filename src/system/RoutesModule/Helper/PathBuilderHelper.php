<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Helper;

use Exception;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Bundle\AbstractBundle;
use Zikula\RoutesModule\Entity\RouteEntity;

class PathBuilderHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns the route's path prepended with the bundle prefix.
     */
    public function getPathWithBundlePrefix(RouteEntity $route): string
    {
        $options = $route->getOptions();
        if (isset($options['zkNoBundlePrefix']) && $options['zkNoBundlePrefix']) {
            // return path only
            return $route->getPath();
        }

        /**
         * @var AbstractBundle $bundle
         */
        $bundle = null;
        try {
            $bundle = $this->kernel->getBundle($route->getBundle());
        } catch (Exception $exception) {
            return $route->getPath();
        }

        // return path prepended with bundle prefix
        return '/' . $bundle->getMetaData()->getUrl() . $route->getPath();
    }
}
