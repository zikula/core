<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use RandomLib\Factory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Core\CoreEvents;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

/**
 * Class AjaxUpgradeController
 */
class AjaxUpgradeController extends AbstractController
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    /**
     * @var string the currently installed core version
     */
    private $currentVersion;

    /**
     * AjaxUpgradeController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $originalParameters = Yaml::parse(file_get_contents($this->container->get('kernel')->getRootDir() .'/config/parameters.yml'));
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
        // load and set new default values from the original parameters.yml file into the custom_parameters.yml file.
        $this->yamlManager->setParameters(array_merge($originalParameters['parameters'], $this->yamlManager->getParameters()));
        $this->currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
    }

    public function ajaxAction(Request $request)
    {
        $stage = $request->request->get('stage');
        $this->yamlManager->setParameter('upgrading', true);
        $status = $this->executeStage($stage);
        $response = ['status' => (bool) $status];
        if (is_array($status)) {
            $response['results'] = $status;
        }

        return new JsonResponse($response);
    }

    public function commandLineAction($stage)
    {
        $this->yamlManager->setParameter('upgrading', true);

        return $this->executeStage($stage);
    }

    private function executeStage($stageName)
    {
        switch ($stageName) {
            case "loginadmin":
                $params = $this->decodeParameters($this->yamlManager->getParameters());

                return $this->loginAdmin($params);
            case "upgrade_event":
                return $this->fireEvent(CoreEvents::CORE_UPGRADE_PRE_MODULE, ['currentVersion' => $this->currentVersion]);
            case "upgrademodules":
                $result = $this->upgradeModules();
                if (count($result) === 0) {
                    return true;
                }

                return $result;
            case "regenthemes":
                return $this->regenerateThemes();
            case "versionupgrade":
                return $this->versionUpgrade();
            case "finalizeparameters":
                return $this->finalizeParameters();
            case "clearcaches":
                return $this->clearCaches();
        }

        return true;
    }

    /**
     * Attempt to upgrade ALL the core modules. Some will need it, some will not.
     * Modules that do not need upgrading return TRUE as a result of the upgrade anyway.
     * @return array
     */
    private function upgradeModules()
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
        ];
        $result = [];
        foreach ($coreModulesInPriorityUpgradeOrder as $moduleName) {
            $extensionEntity = $this->container->get('zikula_extensions_module.extension_repository')->get($moduleName);
            if (isset($extensionEntity)) {
                $result[$moduleName] = $this->container->get('zikula_extensions_module.extension_helper')->upgrade($extensionEntity);
            }
        }

        return $result;
    }

    private function regenerateThemes()
    {
        // regenerate the themes list
        $this->container->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();
        // set all themes as active @todo this is probably overkill
        $themes = $this->container->get('zikula_theme_module.theme_entity.repository')->findAll();
        /** @var \Zikula\ThemeModule\Entity\ThemeEntity $theme */
        foreach ($themes as $theme) {
            $theme->setState(ThemeEntityRepository::STATE_ACTIVE);
        }
        $this->container->get('doctrine')->getManager()->flush();

        return true;
    }

    private function versionUpgrade()
    {
        $doctrine = $this->container->get('doctrine');
        /**
         * NOTE: There are *intentionally* no `break` statements within each case here so that the process continues
         * through each case until the end.
         */
        switch ($this->currentVersion) {
            case '1.4.3':
                $this->installModule('ZikulaMenuModule');
                $this->reSyncAndActivateModules();
                $this->setModuleCategory('ZikulaMenuModule', $this->translator->__('Content'));
            case '1.4.4':
                // nothing
            case '1.4.5':
                // Menu module was introduced in 1.4.4 but not installed on upgrade
                $schemaManager = $doctrine->getConnection()->getSchemaManager();
                if (!$schemaManager->tablesExist(['menu_items'])) {
                    $this->installModule('ZikulaMenuModule');
                    $this->reSyncAndActivateModules();
                    $this->setModuleCategory('ZikulaMenuModule', $this->translator->__('Content'));
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
                    $sql = "DROP TABLE $table;";
                    $connection = $doctrine->getConnection();
                    $stmt = $connection->prepare($sql);
                    $stmt->execute();
                    $stmt->closeCursor();
                }
                $variableApi = $this->container->get('zikula_extensions_module.api.variable');
                $variableApi->del(VariableApi::CONFIG, 'metakeywords');
                if ($this->container->getParameter('datadir') == 'userdata') {
                    $this->yamlManager->setParameter('datadir', 'web/uploads');
                    $fs = $this->container->get('filesystem');
                    $src = realpath(__DIR__ . '/../../../../../');
                    try {
                        if ($fs->exists($src . '/userdata')) {
                            $fs->mirror($src . '/userdata', $src . '/web/uploads');
                        }
                    } catch (\Exception $e) {
                        $this->container->get('session')->getFlashBag()->add('info', $this->translator->__('Attempt to copy files from `userdata` to `web/uploads` failed. You must manually copy the contents.'));
                    }
                }
                // remove legacy blocks
                $blocksToRemove = $doctrine->getRepository(BlockEntity::class)->findBy(['bkey' => ['Extmenu', 'Menutree', 'Menu']]);
                foreach ($blocksToRemove as $block) {
                    $doctrine->getManager()->remove($block);
                }
                $doctrine->getManager()->flush();
        }

        // always do this
        $this->reSyncAndActivateModules();

        return true;
    }

    private function finalizeParameters()
    {
        $variableApi = $this->container->get('zikula_extensions_module.api.variable');
        // Set the System Identifier as a unique string.
        if (!$variableApi->get(VariableApi::CONFIG, 'system_identifier')) {
            $variableApi->set(VariableApi::CONFIG, 'system_identifier', str_replace('.', '', uniqid(rand(1000000000, 9999999999), true)));
        }

        // add new configuration parameters
        $params = $this->yamlManager->getParameters();
        unset($params['username'], $params['password']);
        $RandomLibFactory = new Factory();
        $generator = $RandomLibFactory->getMediumStrengthGenerator();

        if (!isset($params['secret']) || ($params['secret'] == 'ThisTokenIsNotSoSecretChangeIt')) {
            $params['secret'] = $generator->generateString(50);
        }
        if (!isset($params['url_secret'])) {
            $params['url_secret'] = $generator->generateString(10);
        }
        // Configure the Request Context
        // see http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally
        $request = $this->container->get('request_stack')->getMasterRequest();
        $hostFromRequest = isset($request) ? $request->getHost() : null;
        $basePathFromRequest = isset($request) ? $request->getBasePath() : null;
        $params['router.request_context.host'] = isset($params['router.request_context.host']) ? $params['router.request_context.host'] : $hostFromRequest;
        $params['router.request_context.scheme'] = isset($params['router.request_context.scheme']) ? $params['router.request_context.scheme'] : 'http';
        $params['router.request_context.base_url'] = isset($params['router.request_context.base_url']) ? $params['router.request_context.base_url'] : $basePathFromRequest;

        // set currently installed version into parameters
        $params[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM] = ZikulaKernel::VERSION;

        // disable asset combination on upgrades
        $params['zikula_asset_manager.combine'] = false;

        // always try to update the database_server_version param
        try {
            $dbh = new \PDO("$params[database_driver]:host=$params[database_host];dbname=$params[database_name]", $params['database_user'], $params['database_password']);
            $params['database_server_version'] = $dbh->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Exception $e) {
            // do nothing on fail
        }

        unset($params['upgrading']);
        $this->yamlManager->setParameters($params);

        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        $variableApi->set(VariableApi::CONFIG, 'Version_Num', ZikulaKernel::VERSION);
        $variableApi->set(VariableApi::CONFIG, 'Version_Sub', ZikulaKernel::VERSION_SUB);

        // set the 'start' page information to empty to avoid missing module errors.
        $variableApi->set(VariableApi::CONFIG, 'startpage', '');
        $variableApi->set(VariableApi::CONFIG, 'startargs', '');

        return true;
    }

    private function clearCaches()
    {
        // clear cache with zikula's method
        $cacheClearer = $this->container->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony');
        // use full symfony cache_clearer not zikula's to clear entire cache and set for warmup
        // console commands always run in `dev` mode but site should be `prod` mode. clear both for good measure.
        $this->container->get('cache_clearer')->clear('dev');
        $this->container->get('cache_clearer')->clear('prod');
        if (!in_array($this->container->getParameter('env'), ['dev', 'prod'])) {
            // this is just in case anyone ever creates a mode that isn't dev|prod
            $this->container->get('cache_clearer')->clear($this->container->getParameter('env'));
        }

        return true;
    }
}
