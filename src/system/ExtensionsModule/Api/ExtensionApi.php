<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Api;

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

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
     * @var ExtensionRepositoryInterface
     */
    private $repository;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * ExtensionVar constructor.
     * @param ExtensionRepositoryInterface $repository
     * @param KernelInterface $kernel
     */
    public function __construct(ExtensionRepositoryInterface $repository, KernelInterface $kernel)
    {
        $this->repository = $repository;
        $this->kernel = $kernel;
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return ExtensionEntity[]
     */
    public function getModulesBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * return one ExtensionEntity
     * @param $moduleName
     * @return ExtensionEntity
     */
    public function getModule($moduleName)
    {
        return $this->repository->get($moduleName);
    }

    /**
     * @param $name
     * @deprecated remove at Core-2.0
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

    public function isCoreModule($moduleName)
    {
        return in_array($moduleName, [
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
        ]);
    }
}
