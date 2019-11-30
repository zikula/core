<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\VariableApi;

class ModuleManager
{
    public function installModule(string $moduleName): bool
    {
        $module = $this->container->get('kernel')->getModule($moduleName);
        /** @var AbstractCoreModule $module */
        $className = $module->getInstallerClass();
        $reflectionInstaller = new ReflectionClass($className);
        $installer = $reflectionInstaller->newInstance();
        $installer->setBundle($module);
        if ($installer instanceof ContainerAwareInterface) {
            $installer->setContainer($this->container);
        }

        if ($installer->install()) {
            return true;
        }

        return false;
    }

    /**
     * Set an admin category for a module or set to default.
     */
    private function setModuleCategory(string $moduleName, string $translatedCategoryName): bool
    {
        $doctrine = $this->container->get('doctrine');
        $categoryRepository = $doctrine->getRepository('ZikulaAdminModule:AdminCategoryEntity');
        $modulesCategories = $categoryRepository->getIndexedCollection('name');
        $moduleEntity = $doctrine->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->findOneBy(['name' => $moduleName]);

        $moduleRepository = $doctrine->getRepository('ZikulaAdminModule:AdminModuleEntity');
        if (isset($modulesCategories[$translatedCategoryName])) {
            $moduleRepository->setModuleCategory($moduleEntity, $modulesCategories[$translatedCategoryName]);
        } else {
            $defaultCategoryId = $this->container->get(VariableApi::class)->get('ZikulaAdminModule', 'defaultcategory', 5);
            $defaultCategory = $categoryRepository->find($defaultCategoryId);
            $moduleRepository->setModuleCategory($moduleEntity, $defaultCategory);
        }

        return true;
    }

    public function categorizeModules(): bool
    {
        reset(ZikulaKernel::$coreModules);
        $systemModulesCategories = [
            'ZikulaExtensionsModule' => $this->translator->__('System'),
            'ZikulaPermissionsModule' => $this->translator->__('Users'),
            'ZikulaGroupsModule' => $this->translator->__('Users'),
            'ZikulaBlocksModule' => $this->translator->__('Layout'),
            'ZikulaUsersModule' => $this->translator->__('Users'),
            'ZikulaZAuthModule' => $this->translator->__('Users'),
            'ZikulaThemeModule' => $this->translator->__('Layout'),
            'ZikulaSecurityCenterModule' => $this->translator->__('Security'),
            'ZikulaCategoriesModule' => $this->translator->__('Content'),
            'ZikulaMailerModule' => $this->translator->__('System'),
            'ZikulaSearchModule' => $this->translator->__('Content'),
            'ZikulaAdminModule' => $this->translator->__('System'),
            'ZikulaSettingsModule' => $this->translator->__('System'),
            'ZikulaRoutesModule' => $this->translator->__('System'),
            'ZikulaMenuModule' => $this->translator->__('Content'),
        ];

        foreach (ZikulaKernel::$coreModules as $systemModule => $bundleClass) {
            $this->setModuleCategory($systemModule, $systemModulesCategories[$systemModule]);
        }

        return true;
    }
}
