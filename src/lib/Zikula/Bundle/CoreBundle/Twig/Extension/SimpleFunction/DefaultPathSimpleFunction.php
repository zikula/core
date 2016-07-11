<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
