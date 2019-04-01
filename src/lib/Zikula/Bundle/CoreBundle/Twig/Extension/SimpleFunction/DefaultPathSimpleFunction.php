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

namespace Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction;

use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;

class DefaultPathSimpleFunction
{
    /**
     * @var CoreExtension
     */
    private $coreExtension;

    public function __construct(CoreExtension $coreExtension)
    {
        $this->coreExtension = $coreExtension;
    }

    public function getDefaultPath(string $extensionName, string $type = CapabilityApiInterface::USER): string
    {
        $container = $this->coreExtension->getContainer();
        $capability = $container->get('zikula_extensions_module.api.capability')->isCapable($extensionName, $type);
        if (!$capability) {
            return '';
        }
        if (isset($capability['route'])) {
            return $container->get('router')->generate($capability['route']);
        }

        return '';
    }
}
