<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
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

namespace Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;

class DefaultPathSimpleFunction
{
    private $container;

    /**
     * DefaultPathSimpleFunction constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getDefaultPath($extensionName, $type = CapabilityApiInterface::USER)
    {
        $capability = $this->container->get('zikula_extensions_module.api.capability')->isCapable($extensionName, $type);

        return ($capability) ? $this->container->get('router')->generate($capability['route']) : '';
    }
}
