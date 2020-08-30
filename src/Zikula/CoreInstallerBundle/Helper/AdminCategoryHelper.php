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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class AdminCategoryHelper
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var AdminCategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var AdminModuleRepositoryInterface
     */
    private $adminModuleRepository;

    /**
     * @var AdminCategoryEntity[]
     */
    private $extensionCategories;

    /**
     * @var AdminCategoryEntity
     */
    private $defaultCategory;

    public function __construct(
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
        $this->categoryRepository = $this->managerRegistry->getRepository(AdminCategoryEntity::class);
        $this->adminModuleRepository = $this->managerRegistry->getRepository(AdminModuleEntity::class);
    }

    public function categorize(): bool
    {
        reset(ZikulaKernel::$coreExtension);
        $systemModulesCategories = [
            'ZikulaExtensionsModule' => $this->translator->trans('System'),
            'ZikulaPermissionsModule' => $this->translator->trans('Users'),
            'ZikulaGroupsModule' => $this->translator->trans('Users'),
            'ZikulaBlocksModule' => $this->translator->trans('Layout'),
            'ZikulaUsersModule' => $this->translator->trans('Users'),
            'ZikulaZAuthModule' => $this->translator->trans('Users'),
            'ZikulaThemeModule' => $this->translator->trans('Layout'),
            'ZikulaSecurityCenterModule' => $this->translator->trans('Security'),
            'ZikulaCategoriesModule' => $this->translator->trans('Content'),
            'ZikulaMailerModule' => $this->translator->trans('System'),
            'ZikulaSearchModule' => $this->translator->trans('Content'),
            'ZikulaAdminModule' => $this->translator->trans('System'),
            'ZikulaSettingsModule' => $this->translator->trans('System'),
            'ZikulaRoutesModule' => $this->translator->trans('System'),
            'ZikulaMenuModule' => $this->translator->trans('Content'),
            'ZikulaAtomTheme' => $this->translator->trans('Layout'),
            'ZikulaBootstrapTheme' => $this->translator->trans('Layout'), // @deprecated remove at Core-4.0.0
            'ZikulaDefaultTheme' => $this->translator->trans('Layout'),
            'ZikulaPrinterTheme' => $this->translator->trans('Layout'),
            'ZikulaRssTheme' => $this->translator->trans('Layout'),
        ];
        $this->setUp();

        foreach (ZikulaKernel::$coreExtension as $systemModule => $bundleClass) {
            $this->setCategory($systemModule, $systemModulesCategories[$systemModule]);
        }

        return true;
    }

    /**
     * Set an admin category for an extension or set to default.
     */
    public function setCategory(string $moduleName, string $translatedCategoryName): void
    {
        $moduleEntity = $this->managerRegistry->getRepository(ExtensionEntity::class)
            ->findOneBy(['name' => $moduleName]);
        if (empty($this->extensionCategories)) {
            $this->setUp();
        }

        if (isset($this->extensionCategories[$translatedCategoryName])) {
            $this->adminModuleRepository->setModuleCategory($moduleEntity, $this->extensionCategories[$translatedCategoryName]);
        } else {
            $this->adminModuleRepository->setModuleCategory($moduleEntity, $this->defaultCategory);
        }
    }

    private function setUp(): void
    {
        $this->extensionCategories = $this->categoryRepository->getIndexedCollection('name');
        $defaultCategoryId = $this->variableApi->get('ZikulaAdminModule', 'defaultcategory', 5);
        $this->defaultCategory = $this->categoryRepository->find($defaultCategoryId);
    }
}
