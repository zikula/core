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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Collector\InstallerCollector;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

class CoreInstallerExtensionHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var YamlDumper
     */
    private $yamlHelper;

    /**
     * @var BundleSyncHelper
     */
    private $bundleSyncHelper;

    /**
     * @var ExtensionHelper
     */
    private $extensionHelper;

    /**
     * @var InstallerCollector
     */
    private $installerCollector;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var SessionInterface
     */
    private $session;

    private $adminCategoryHelper;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        ParameterHelper $parameterHelper,
        BundleSyncHelper $bundleSyncHelper,
        ExtensionHelper $extensionHelper,
        InstallerCollector $installerCollector,
        VariableApiInterface $variableApi,
        SessionInterface $session,
        AdminCategoryHelper $adminCategoryHelper
    ) {
        $this->kernel = $kernel;
        $this->managerRegistry = $managerRegistry;
        $this->translator = $translator;
        $this->yamlHelper = $parameterHelper->getYamlHelper();
        $this->bundleSyncHelper = $bundleSyncHelper;
        $this->extensionHelper = $extensionHelper;
        $this->installerCollector = $installerCollector;
        $this->variableApi = $variableApi;
        $this->session = $session;
        $this->adminCategoryHelper = $adminCategoryHelper;
    }

    public function install(string $extensionName): bool
    {
        $extensionBundle = $this->kernel->getModule($extensionName);
        /** @var AbstractExtension $extensionBundle */
        $className = $extensionBundle->getInstallerClass();
        $installer = $this->installerCollector->get($className);

        if ($installer->install()) {
            return true;
        }

        return false;
    }

    /**
     * Scan the filesystem and sync the extensions table. Set all system extensions to active state.
     */
    public function reSyncAndActivate(): bool
    {
        $extensionsInFileSystem = $this->bundleSyncHelper->scanForBundles(true);
        $this->bundleSyncHelper->syncExtensions($extensionsInFileSystem);

        /** @var ExtensionEntity[] $extensions */
        $extensions = $this->managerRegistry->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->findBy(['name' => array_keys(ZikulaKernel::$coreExtension)]);
        foreach ($extensions as $extension) {
            $extension->setState(Constant::STATE_ACTIVE);
        }
        $this->managerRegistry->getManager()->flush();

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
            $extensionEntity = $this->managerRegistry->getRepository(ExtensionEntity::class)->get($moduleName);
            if (isset($extensionEntity)) {
                $result = $result && $this->extensionHelper->upgrade($extensionEntity);
            }
        }

        return $result;
    }

    public function executeCoreMetaUpgrade($currentCoreVersion): bool
    {
        /**
         * NOTE: There are *intentionally* no `break` statements within each case here so that the process continues
         * through each case until the end.
         */
        switch ($currentCoreVersion) {
            case '1.4.3':
                $this->install('ZikulaMenuModule');
                $this->reSyncAndActivate();
                $this->adminCategoryHelper->setCategory('ZikulaMenuModule', $this->translator->trans('Content'));
            case '1.4.4':
                // nothing
            case '1.4.5':
                // Menu module was introduced in 1.4.4 but not installed on upgrade
                $schemaManager = $this->managerRegistry->getConnection()->getSchemaManager();
                if (!$schemaManager->tablesExist(['menu_items'])) {
                    $this->install('ZikulaMenuModule');
                    $this->reSyncAndActivate();
                    $this->adminCategoryHelper->setCategory('ZikulaMenuModule', $this->translator->trans('Content'));
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
                    $connection = $this->managerRegistry->getConnection();
                    $stmt = $connection->prepare($sql);
                    $stmt->execute();
                    $stmt->closeCursor();
                }
                $this->variableApi->del(VariableApi::CONFIG, 'metakeywords');
                $this->variableApi->del(VariableApi::CONFIG, 'startpage');
                $this->variableApi->del(VariableApi::CONFIG, 'startfunc');
                $this->variableApi->del(VariableApi::CONFIG, 'starttype');
                if ('userdata' === $this->yamlHelper->getParameter('datadir')) {
                    $this->yamlHelper->setParameter('datadir', 'public/uploads');
                    $fs = new Filesystem();
                    $src = $this->kernel->getProjectDir();
                    try {
                        if ($fs->exists($src . '/userdata')) {
                            $fs->mirror($src . '/userdata', $src . '/public/uploads');
                        }
                    } catch (\Exception $exception) {
                        $this->session->getFlashBag()->add(
                            'info',
                            'Attempt to copy files from `userdata` to `public/uploads` failed. You must manually copy the contents.'
                        );
                    }
                }
                // remove legacy blocks
                $blocksToRemove = $this->managerRegistry->getRepository(BlockEntity::class)->findBy(['blocktype' => ['Extmenu', 'Menutree', 'Menu']]);
                foreach ($blocksToRemove as $block) {
                    $this->managerRegistry->getManager()->remove($block);
                }
                $this->managerRegistry->getManager()->flush();
            case '2.0.15':
                // nothing
            case '3.0.0':
                // current version - cannot perform anything yet
        }

        // always do this
        $this->reSyncAndActivate();
        // set default themes to ZikulaBootstrapTheme
        $this->variableApi->set(VariableApi::CONFIG, 'Default_Theme', 'ZikulaBootstrapTheme');
        $this->variableApi->set('ZikulaAdminModule', 'admintheme', '');
        // unset start page information to avoid missing module errors
        $this->variableApi->set(VariableApi::CONFIG, 'startController_en', '');

        return true;
    }
}
