<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Api;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Constant;

/**
 * @deprecated remove at Core-2.0
 * Class ExtensionApi
 */
class ExtensionApi
{
    /**
     * @deprecated
     */
    const STATE_UNINITIALISED = Constant::STATE_UNINITIALISED;

    /**
     * @deprecated
     */
    const STATE_INACTIVE = Constant::STATE_INACTIVE;

    /**
     * @deprecated
     */
    const STATE_ACTIVE = Constant::STATE_ACTIVE;

    /**
     * @deprecated
     */
    const STATE_MISSING = Constant::STATE_MISSING;

    /**
     * @deprecated
     */
    const STATE_UPGRADED = Constant::STATE_UPGRADED;

    /**
     * @deprecated
     */
    const STATE_NOTALLOWED = Constant::STATE_NOTALLOWED;

    /**
     * @deprecated
     */
    const STATE_INVALID = Constant::STATE_INVALID;

    /**
     * @deprecated
     */
    const INCOMPATIBLE_CORE_SHIFT = Constant::INCOMPATIBLE_CORE_SHIFT;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * ExtensionVar constructor.
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Check if the module is loaded into the kernel or not. Returns null if not.
     * @param $name
     * @deprecated remove at Core-2.0 replace with $kernel->getModule($name) when all modules are Bundles
     * @return null|\Zikula\Core\AbstractBundle
     */
    public function getModuleInstanceOrNull($name)
    {
        $moduleInstance = null;
        try {
            $moduleInstance = $this->kernel->getModule($name);
        } catch (\Exception $e) {
            // silent fail
            // @todo remove this try/catch block at Core-2.0
        }

        return $moduleInstance;
    }

    /**
     * Checks if name is is the list of core modules.
     * @deprecated remove at Core-2.0 use Kernel method directly
     * @param $moduleName
     * @return bool
     */
    public function isCoreModule($moduleName)
    {
        return ZikulaKernel::isCoreModule($moduleName);
    }
}
