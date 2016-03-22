<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction;

use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;

class DefaultPathSimpleFunction
{
    private $coreExtension;

    /**
     * DefaultPathSimpleFunction constructor.
     * @param CoreExtension $coreExtension
     */
    public function __construct(CoreExtension $coreExtension)
    {
        $this->coreExtension = $coreExtension;
    }

    public function getDefaultPath($extensionName, $type = CapabilityApiInterface::USER)
    {
        $container = $this->coreExtension->getContainer();
        $capability = $container->get('zikula_extensions_module.api.capability')->isCapable($extensionName, $type);
        if (!$capability) {
            return '';
        }
        if (isset($capability['route'])) {
            return $container->get('router')->generate($capability['route']);
        } elseif (isset($capability['url'])) {
            // BC - remove at Core-2.0
            return $capability['url'];
        }

        return '';
    }
}
