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

use Symfony\Component\HttpKernel\KernelInterface;

class ExtensionApi
{
    const STATE_UNINITIALISED = 1;
    const STATE_INACTIVE = 2;
    const STATE_ACTIVE = 3;
    const STATE_MISSING = 4;
    const STATE_UPGRADED = 5;
    const STATE_NOTALLOWED = 6;
    const STATE_INVALID = -1;
    const INCOMPATIBLE_CORE_SHIFT = 20;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var array public list of all the core modules
     */
    public $coreModules = [
        'ZikulaAdminModule',
        'ZikulaBlocksModule',
        'ZikulaCategoriesModule',
        'ZikulaExtensionsModule',
        'ZikulaGroupsModule',
        'ZikulaMailerModule',
        'ZikulaPermissionsModule',
        'ZikulaRoutesModule',
        'ZikulaSearchModule',
        'ZikulaSecurityCenterModule',
        'ZikulaSettingsModule',
        'ZikulaThemeModule',
        'ZikulaUsersModule',
        'ZikulaZAuthModule',
    ];

    /**
     * ExtensionVar constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
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
     * @param $moduleName
     * @return bool
     */
    public function isCoreModule($moduleName)
    {
        return in_array($moduleName, $this->coreModules);
    }
}
