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
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\Bundle\CoreBundle\DependencyInjection\Configuration;
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
                // no break
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
                // no break
            case '1.4.6':
                // nothing needed
            case '1.4.7':
                // nothing needed
            case '1.5.0':
                // nothing needed
            case '1.9.99':
                // upgrades required for 2.0.0
                /** @var \Doctrine\DBAL\Driver\PDOConnection $connection */
                $connection = $this->managerRegistry->getConnection();
                foreach (['objectdata_attributes', 'objectdata_log', 'objectdata_meta', 'workflows', 'categories_mapobj'] as $table) {
                    $sql = "DROP TABLE IF EXISTS ${table};";
                    $connection->executeQuery($sql);
                }
                $connection->executeQuery(
                    "DELETE FROM module_vars WHERE modname LIKE 'systemplugin%'"
                );

                $this->variableApi->del(VariableApi::CONFIG, 'metakeywords');
                $this->variableApi->del(VariableApi::CONFIG, 'startpage');
                $this->variableApi->del(VariableApi::CONFIG, 'startfunc');
                $this->variableApi->del(VariableApi::CONFIG, 'starttype');
                $projectDir = $this->kernel->getProjectDir();
                if (file_exists($projectDir . '/config/services_custom.yaml')) {
                    $fs = new Filesystem();
                    $yamlHelper = new YamlDumper($projectDir . '/config', 'services_custom.yaml');
                    if ('userdata' === $yamlHelper->getParameter('datadir')) {
                        try {
                            if ($fs->exists($projectDir . '/userdata')) {
                                $fs->mirror($projectDir . '/userdata', $projectDir . '/public/uploads');
                            }
                        } catch (\Exception $exception) {
                            $this->session->getFlashBag()->add(
                                'info',
                                'Attempt to copy files from `userdata` to `public/uploads` failed. You must manually copy the contents.'
                            );
                        }
                    }
                }
                // remove legacy blocks
                $blocksToRemove = $this->managerRegistry->getRepository(BlockEntity::class)->findBy(['blocktype' => ['Extmenu', 'Menutree', 'Menu']]);
                foreach ($blocksToRemove as $block) {
                    $this->managerRegistry->getManager()->remove($block);
                }
                $this->managerRegistry->getManager()->flush();
                // no break
            case '2.0.15':
                // nothing
            case '3.0.0':
                // nothing
            case '3.0.99':
                $this->migrateParamsAndDynamicConfig();
                // no break
            case '3.1.0':
                // current version - cannot perform anything yet
        }

        // always do this
        $this->reSyncAndActivate();
        // set default themes to ZikulaBootstrapTheme
        $this->variableApi->set(VariableApi::CONFIG, 'Default_Theme', 'ZikulaBootstrapTheme');
        $this->variableApi->set('ZikulaAdminModule', 'admintheme', '');
        // unset start page information to avoid missing module errors
        $this->variableApi->set(VariableApi::CONFIG, 'startController_en', []);

        return true;
    }

    /**
     * Migrate all custom values formerly in services_custom.yaml and dynamic/generated.yaml
     * to real package config definitions.
     * Delete the legacy files.
     */
    private function migrateParamsAndDynamicConfig(): void
    {
        $fs = new Filesystem();
        $projectDir = $this->kernel->getProjectDir();
        if (file_exists($path = $projectDir . '/config/services_custom.yaml')) {
            $servicesCustom = Yaml::parse(file_get_contents($path));
        }
        if (file_exists($path = $projectDir . '/config/dynamic/generated.yaml')) {
            $dynamicConfig = Yaml::parse(file_get_contents($path));
        }
        $configurator = new Configurator($projectDir);
        $configurator->loadPackages(['core', 'zikula_routes', 'zikula_security_center', 'zikula_settings', 'zikula_theme']);

        $configurator->set('core', 'datadir', $servicesCustom['parameters']['datadir'] ?? Configuration::DEFAULT_DATADIR);
        $configurator->set('core', 'maker_root_namespace', $dynamicConfig['maker']['root_namespace'] ?? null);
        $configurator->set('core', 'multisites', $dynamicConfig['parameters']['multisites'] ?? $configurator->get('core', 'multisites'));

        $configurator->set('zikula_routes', 'jms_i18n_routing_strategy', $dynamicConfig['jms_i18n_routing']['strategy'] ?? 'prefix_except_default');

        $configurator->set('zikula_security_center', 'x_frame_options', $servicesCustom['security.x_frame_options'] ?? 'SAMEORIGIN');
        $session = [
            'name' => $dynamicConfig['parameters']['zikula.session.name'] ?? '_zsid',
            'handler_id' => $dynamicConfig['parameters']['zikula.session.handler_id'] ?? 'session.handler.native_file',
            'storage_id' => $dynamicConfig['parameters']['zikula.session.storage_id'] ?? 'zikula_core.bridge.http_foundation.zikula_session_storage_file',
            'save_path' => $dynamicConfig['parameters']['zikula.session.save_path'] ?? '%kernel.cache_dir%/sessions',
            'cookie_secure' => 'auto'
        ];
        $configurator->set('zikula_security_center', 'session', $session);

        $configurator->set('zikula_settings', 'locale', $servicesCustom['parameters']['locale'] ?? 'en');
        $configurator->set('zikula_settings', 'locales', $dynamicConfig['parameters']['localisation.locales'] ?? ['en']);

        $configurator->set('zikula_theme', 'script_position', $servicesCustom['parameters']['script_position'] ?? 'foot');
        $configurator->set('zikula_theme', 'font_awesome_path', $servicesCustom['parameters']['zikula.stylesheet.fontawesome.min.path'] ?? '/font-awesome/css/all.min.css');
        $bootstrap = [
            'css_path' => $servicesCustom['parameters']['zikula.javascript.bootstrap.min.path'] ?? '/bootstrap/css/bootstrap.min.css',
            'js_path' => $servicesCustom['parameters']['zikula.stylesheet.bootstrap.min.path'] ?? '/bootstrap/js/bootstrap.bundle.min.js'
        ];
        $configurator->set('zikula_theme', 'bootstrap', $bootstrap);
        $assetManager = [
            'combine' => $servicesCustom['parameters']['zikula_asset_manager.combine'] ?? false,
            'lifetime' => $servicesCustom['parameters']['zikula_asset_manager.lifetime'] ?? '1 day',
            'compress' => $servicesCustom['parameters']['zikula_asset_manager.compress'] ?? true,
            'minify' => $servicesCustom['parameters']['zikula_asset_manager.minify'] ?? true
        ];
        $configurator->set('zikula_theme', 'asset_manager', $assetManager);

        $configurator->write(); // writes nothing when value = default

        try {
            $fs->remove([
                $projectDir . '/config/dynamic',
                $projectDir . '/config/services_custom.yaml'
            ]);
        } catch (\Exception $exception) {
            // silently fail
        }
    }
}
