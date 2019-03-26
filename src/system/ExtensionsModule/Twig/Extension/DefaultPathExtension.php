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

namespace Zikula\ExtensionsModule\Twig\Extension;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;

class DefaultPathExtension extends AbstractExtension
{
    /**
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * DefaultPathExtension constructor.
     * @param CapabilityApiInterface $capabilityApi
     * @param RouterInterface $router
     */
    public function __construct(
        CapabilityApiInterface $capabilityApi,
        RouterInterface $router
    ) {
        $this->capabilityApi = $capabilityApi;
        $this->router = $router;
    }

    public function getDefaultPath($extensionName, $type = CapabilityApiInterface::USER)
    {
        $capability = $this->capabilityApi->isCapable($extensionName, $type);
        if (!$capability) {
            return '';
        }
        if (isset($capability['route'])) {
            return $this->router->generate($capability['route']);
        }

        return '';
    }
}
