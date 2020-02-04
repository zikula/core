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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\ExtensionsModule\AbstractCoreModule;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

class CoreInstallerExtensionHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var YamlDumper
     */
    private $yamlHelper;

    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator,
        ParameterHelper $parameterHelper
    ) {
        $this->container = $container;
        $this->translator = $translator;
        $this->yamlHelper = $parameterHelper->getYamlHelper();
    }

    public function install(string $moduleName): bool
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
     * Set an admin category for an extension or set to default.
     */
    private function setCategory(string $moduleName, string $translatedCategoryName): bool
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
            'ZikulaBootstrapTheme' => $this->translator->trans('Layout'),
            'ZikulaPrinterTheme' => $this->translator->trans('Layout'),
            'ZikulaRssTheme' => $this->translator->trans('Layout'),
        ];

        foreach (ZikulaKernel::$coreExtension as $systemModule => $bundleClass) {
            $this->setCategory($systemModule, $systemModulesCategories[$systemModule]);
        }

        return true;
    }

    /**
     * Scan the filesystem and sync the extensions table. Set all system extensions to active state.
     */
    public function reSyncAndActivate(): bool
    {
        $bundleSyncHelper = $this->container->get(BundleSyncHelper::class);
        $extensionsInFileSystem = $bundleSyncHelper->scanForBundles(true);
        $bundleSyncHelper->syncExtensions($extensionsInFileSystem);

        $doctrine = $this->container->get('doctrine');

        /** @var ExtensionEntity[] $extensions */
        $extensions = $doctrine->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->findBy(['name' => array_keys(ZikulaKernel::$coreExtension)]);
        foreach ($extensions as $extension) {
            $extension->setState(Constant::STATE_ACTIVE);
        }
        $doctrine->getManager()->flush();

        return true;
    }

    /**
     * Attempt to upgrade ALL the core extensions. Some will need it, some will not.
     * Extensions that do not need upgrading return TRUE as a result of the upgrade anyway.
     */
    public function upgrade(): bool
    {
        $coreModulesInPriorityUpgradeOrder = [
            'ZikulaExtensionsModule',
            'ZikulaUsersModule',
            'ZikulaZAuthModule',
            'ZikulaGroupsModule',
            'ZikulaPermissionsModule',
            'ZikulaAdminModule',
            'ZikulaBlocksModule',
            'ZikulaThemeModule',
            'ZikulaSettingsModule',
            'ZikulaCategoriesModule',
            'ZikulaSecurityCenterModule',
            'ZikulaRoutesModule',
            'ZikulaMailerModule',
            'ZikulaSearchModule',
            'ZikulaMenuModule',
            'ZikulaBootstrapTheme',
            'ZikulaAtomTheme',
            'ZikulaRssTheme',
            'ZikulaPrinterTheme',
        ];
        $result = true;
        foreach ($coreModulesInPriorityUpgradeOrder as $moduleName) {
            $extensionEntity = $this->container->get('doctrine')->getRepository('ZikulaExtensionsModule:ExtensionEntity')->get($moduleName);
            if (isset($extensionEntity)) {
                $result = $result && $this->container->get(ExtensionHelper::class)->upgrade($extensionEntity);
            }
        }

        return $result;
    }

    public function executeCoreMetaUpgrade($currentCoreVersion): bool
    {
        $doctrine = $this->container->get('doctrine');
        /**
         * NOTE: There are *intentionally* no `break` statements within each case here so that the process continues
         * through each case until the end.
         */
        switch ($currentCoreVersion) {
            case '1.4.3':
                $this->install('ZikulaMenuModule');
                $this->reSyncAndActivate();
                $this->setCategory('ZikulaMenuModule', $this->translator->trans('Content'));
            case '1.4.4':
                // nothing
            case '1.4.5':
                // Menu module was introduced in 1.4.4 but not installed on upgrade
                $schemaManager = $doctrine->getConnection()->getSchemaManager();
                if (!$schemaManager->tablesExist(['menu_items'])) {
                    $this->install('ZikulaMenuModule');
                    $this->reSyncAndActivate();
                    $this->setCategory('ZikulaMenuModule', $this->translator->trans('Content'));
                }
            case '1.4.6':
                // nothing needed
            case '1.4.7':
                // nothing needed
            case '1.5.0':
                // nothing needed
            case '1.9.99':
                // upgrades required for 2.0.0
                foreach (['objectdata_attributes', 'objectdata_log', 'objectdata_meta', 'workflows'] as $table) {
                    $sql = "DROP TABLE ${table};";
                    /** @var \Doctrine\DBAL\Driver\PDOConnection $connection */
                    $connection = $doctrine->getConnection();
                    $stmt = $connection->prepare($sql);
                    $stmt->execute();
                    $stmt->closeCursor();
                }
                $variableApi = $this->container->get(VariableApi::class);
                $variableApi->del(VariableApi::CONFIG, 'metakeywords');
                $variableApi->del(VariableApi::CONFIG, 'startpage');
                $variableApi->del(VariableApi::CONFIG, 'startfunc');
                $variableApi->del(VariableApi::CONFIG, 'starttype');
                if ('userdata' === $this->container->getParameter('datadir')) {
                    $this->yamlHelper->setParameter('datadir', 'public/uploads');
                    $fs = $this->container->get('filesystem');
                    $src = dirname(__DIR__, 5) . '/';
                    try {
                        if ($fs->exists($src . '/userdata')) {
                            $fs->mirror($src . '/userdata', $src . '/public/uploads');
                        }
                    } catch (\Exception $exception) {
                        $this->container->get('session')->getFlashBag()->add(
                            'info',
                            'Attempt to copy files from `userdata` to `public/uploads` failed. You must manually copy the contents.'
                        );
                    }
                }
                // remove legacy blocks
                $blocksToRemove = $doctrine->getRepository(BlockEntity::class)->findBy(['blocktype' => ['Extmenu', 'Menutree', 'Menu']]);
                foreach ($blocksToRemove as $block) {
                    $doctrine->getManager()->remove($block);
                }
                $doctrine->getManager()->flush();
            case '2.0.0':
                // nothing needed
            case '3.0.0':
                // current version - cannot perform anything yet
        }

        // always do this
        $this->reSyncAndActivate();

        return true;
    }
}
