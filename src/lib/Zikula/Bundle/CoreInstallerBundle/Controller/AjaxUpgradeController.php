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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
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
        $this->container->setParameter('upgrading', true);
        $status = $this->executeStage($stage);
        $response = ['status' => (bool) $status];
        if (is_array($status)) {
            $response['results'] = $status;
        }

        return new JsonResponse($response);
    }

    public function commandLineAction($stage)
    {
        $this->container->setParameter('upgrading', true);

        return $this->executeStage($stage);
    }

    private function executeStage($stageName)
    {
        switch ($stageName) {
            case "loginadmin":
                $params = $this->decodeParameters($this->yamlManager->getParameters());

                return $this->loginAdmin($params);
            case "upgrademodules":
                $result = $this->upgradeModules();
                if (count($result) === 0) {
                    return true;
                }

                return $result;
            case "installroutes":
                if (version_compare(ZikulaKernel::VERSION, '1.4.0', '>') && version_compare($this->currentVersion, '1.4.0', '>=')) {
                    // this stage is not necessary to upgrade from 1.4.0 -> 1.4.x
                    return true;
                }
                $this->installModule('ZikulaRoutesModule');
                $this->reSyncAndActivateModules();
                $this->setModuleCategory('ZikulaRoutesModule', $this->translator->__('System'));

                return true;
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
        /**
         * NOTE: There are *intentionally* no `break` statements within each case here so that the process continues
         * through each case until the end.
         */
        switch ($this->currentVersion) {
            case '1.4.0':
                // perform the following SQL
                //ALTER TABLE categories_category ADD CONSTRAINT FK_D0B2B0F88304AF18 FOREIGN KEY (cr_uid) REFERENCES users (uid);
                //ALTER TABLE categories_category ADD CONSTRAINT FK_D0B2B0F8C072C1DD FOREIGN KEY (lu_uid) REFERENCES users (uid);
                //ALTER TABLE categories_registry ADD CONSTRAINT FK_1B56B4338304AF18 FOREIGN KEY (cr_uid) REFERENCES users (uid);
                //ALTER TABLE categories_registry ADD CONSTRAINT FK_1B56B433C072C1DD FOREIGN KEY (lu_uid) REFERENCES users (uid);
                //ALTER TABLE sc_intrusion ADD CONSTRAINT FK_8595CE46539B0606 FOREIGN KEY (uid) REFERENCES users (uid);
                //DROP INDEX gid_uid ON group_membership;
                //ALTER TABLE group_membership DROP PRIMARY KEY;
                //ALTER TABLE group_membership ADD CONSTRAINT FK_5132B337539B0606 FOREIGN KEY (uid) REFERENCES users (uid);
                //ALTER TABLE group_membership ADD CONSTRAINT FK_5132B3374C397118 FOREIGN KEY (gid) REFERENCES groups (gid);
                //CREATE INDEX IDX_5132B337539B0606 ON group_membership (uid);
                //CREATE INDEX IDX_5132B3374C397118 ON group_membership (gid);
                //ALTER TABLE group_membership ADD PRIMARY KEY (uid, gid);
            case '1.4.1':
                $request = $this->container->get('request_stack')->getCurrentRequest();
                if (isset($request) && $request->hasSession()) {
                    $request->getSession()->remove('interactive_init');
                    $request->getSession()->remove('interactive_remove');
                    $request->getSession()->remove('interactive_upgrade');
                }
            case '1.4.2':
                $this->installModule('ZikulaZAuthModule');
                $this->reSyncAndActivateModules();
                $this->setModuleCategory('ZikulaZAuthModule', $this->translator->__('Users'));
            case '1.4.3':
                $this->installModule('ZikulaMenuModule');
                $this->reSyncAndActivateModules();
                $this->setModuleCategory('ZikulaMenuModule', $this->translator->__('Content'));
            case '1.4.4':
                // nothing
            case '1.4.5':
                // Menu module was introduced in 1.4.4 but not installed on upgrade
                $schemaManager = $this->container->get('doctrine')->getConnection()->getSchemaManager();
                if (!$schemaManager->tablesExist(['menu_items'])) {
                    $this->installModule('ZikulaMenuModule');
                    $this->reSyncAndActivateModules();
                    $this->setModuleCategory('ZikulaMenuModule', $this->translator->__('Content'));
                }
            case '1.4.6':
                // nothing needed
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

        $this->yamlManager->setParameters($params);

        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        $variableApi->set(VariableApi::CONFIG, 'Version_Num', ZikulaKernel::VERSION);
        $variableApi->set(VariableApi::CONFIG, 'Version_ID', \Zikula_Core::VERSION_ID); // @deprecated
        $variableApi->set(VariableApi::CONFIG, 'Version_Sub', ZikulaKernel::VERSION_SUB);

        // set the 'start' page information to empty to avoid missing module errors.
        $variableApi->set(VariableApi::CONFIG, 'startpage', '');
        $variableApi->set(VariableApi::CONFIG, 'starttype', '');
        $variableApi->set(VariableApi::CONFIG, 'startfunc', '');
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
        if (in_array($this->container->getParameter('env'), ['dev', 'prod'])) {
            // this is just in case anyone ever creates a mode that isn't dev|prod
            $this->container->get('cache_clearer')->clear($this->container->getParameter('env'));
        }

        // finally remove upgrading flag in parameters
        $this->yamlManager->delParameter('upgrading');

        return true;
    }
}
