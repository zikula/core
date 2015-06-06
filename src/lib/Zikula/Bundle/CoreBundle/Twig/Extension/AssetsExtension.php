<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
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

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\Twig;

use Symfony\Bundle\TwigBundle\Extension\AssetsExtension as Base;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AssetsExtension extends Base {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getAssetUrl($path, $packageName = null) {
        $url = $this->container->get('templating.helper.assets')->getUrl($path, $packageName);
        if (!$packageName && preg_match("/^(css|images|js)\//", $path)) {
            $assetBase = str_replace($this->container->getParameter('kernel.root_dir') . '/../web', '', $this->container->getParameter('assetic.write_to'));
            $url = "{$assetBase}{$url}";
        }

        return $url;
    }
}
