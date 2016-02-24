<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Api;

use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * Class CapabilityApi
 * @package Zikula\ExtensionsModule
 */
class CapabilityApi implements CapabilityApiInterface
{
    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;
    /**
     * @var ExtensionEntity[]
     */
    private $extensionsByCapability = [];
    /**
     * @var ExtensionEntity[]
     */
    private $extensionsByName = [];

    /**
     * Capability constructor.
     * @param ExtensionRepositoryInterface $extensionRepository
     */
    public function __construct(ExtensionRepositoryInterface $extensionRepository)
    {
        $this->extensionRepository = $extensionRepository;
    }

    /**
     * Load extensions into private property cache.
     */
    private function load()
    {
        $extensions = $this->extensionRepository->findAll();
        /** @var ExtensionEntity $extension */
        foreach ($extensions as $extension) {
            foreach ($extension->getCapabilities() as $capability => $definition) {
                $this->extensionsByCapability[$capability][] = $extension;
            }
            $this->extensionsByName[$extension->getName()] = $extension;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionsCapableOf($capability)
    {
        if (empty($this->extensionsByCapability[$capability])) {
            $this->load();
        }

        return !empty($this->extensionsByCapability[$capability]) ? $this->extensionsByCapability[$capability] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function isCapable($extensionName, $requestedCapability)
    {
        if (empty($this->extensionsByName[$extensionName])) {
            $this->extensionsByName[$extensionName] = $this->extensionRepository->findOneBy(['name' => $extensionName]);
        }
        $capabilities = $this->extensionsByName[$extensionName]->getCapabilities();
        if ($requestedCapability == CapabilityApiInterface::HOOK_SUBSCRIBE_OWN) {
            return array_key_exists(CapabilityApiInterface::HOOK_SUBSCRIBER, $capabilities)
                && array_key_exists($requestedCapability, $capabilities[CapabilityApiInterface::HOOK_SUBSCRIBER]);
        }

        return array_key_exists($requestedCapability, $capabilities)
            ? $capabilities[$requestedCapability]
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilitiesOf($extensionName)
    {
        if (empty($this->extensionsByName[$extensionName])) {
            $this->extensionsByName[$extensionName] = $this->extensionRepository->findOneBy(['name' => $extensionName]);
        }

        return $this->extensionsByName[$extensionName]->getCapabilities();
    }
}
