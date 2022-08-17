<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Api;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class CapabilityApi implements CapabilityApiInterface
{
    /**
     * @var ExtensionEntity[]
     */
    private array $extensionsByCapability = [];

    /**
     * @var ExtensionEntity[]
     */
    private array $extensionsByName = [];

    public function __construct(private readonly ZikulaHttpKernelInterface $kernel)
    {
    }

    /**
     * Load extensions into private property cache.
     */
    private function load(): void
    {
        $extensions = $this->kernel->getModules();
        foreach ($extensions as $extension) {
            foreach ($extension->getMetaData()->getCapabilities() as $capability => $definition) {
                $this->extensionsByCapability[$capability][] = $extension;
            }
            $this->extensionsByName[$extension->getMetaData()->getName()] = $extension;
        }
    }

    public function getExtensionsCapableOf(string $capability): iterable
    {
        if (!isset($this->extensionsByCapability[$capability]) || empty($this->extensionsByCapability[$capability])) {
            $this->load();
        }

        return !empty($this->extensionsByCapability[$capability]) ? $this->extensionsByCapability[$capability] : [];
    }

    public function isCapable(string $extensionName, string $requestedCapability): bool
    {
        if (empty($this->extensionsByName)) {
            $this->load();
        }
        if (!array_key_exists($extensionName, $this->extensionsByName)) {
            return false;
        }

        $capabilities = $this->extensionsByName[$extensionName]->getMetaData()->getCapabilities();

        return array_key_exists($requestedCapability, $capabilities)
            ? $capabilities[$requestedCapability]
            : false;
    }

    public function getCapabilitiesOf(string $extensionName): array
    {
        if (empty($this->extensionsByName)) {
            $this->load();
        }
        if (!array_key_exists($extensionName, $this->extensionsByName)) {
            return [];
        }

        return $this->extensionsByName[$extensionName]->getCapabilities();
    }
}
