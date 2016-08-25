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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreBundle\YamlDumper;
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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
        $this->currentVersion = $this->container->getParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM);
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
                return $this->container->get('core_installer.controller.ajaxinstall')->loginAdmin();
            case "upgrademodules":
                $result = $this->upgradeModules();
                if (count($result) === 0) {
                    return true;
                }

                return $result;
            case "installroutes":
                return $this->installRoutesModule();
            case "reloadroutes":
                return $this->container->get('core_installer.controller.ajaxinstall')->reloadRoutes();
            case "regenthemes":
                return $this->regenerateThemes();
            case "from140to141":
                return $this->from140to141();
            case "from141to142":
                return $this->from141to142();
            case "from142to143":
                return $this->from142to143();
            case "finalizeparameters":
                return $this->finalizeParameters();
            case "clearcaches":
                return $this->clearCaches();
        }
        \System::setInstalling(false);

        return true;
    }

    private function installRoutesModule()
    {
        if (version_compare(\Zikula_Core::VERSION_NUM, '1.4.0', '>') && version_compare($this->currentVersion, '1.4.0', '>=')) {
            // this stage is not necessary to upgrade from 1.4.0 -> 1.4.x
            return true;
        }

        $kernel = $this->container->get('kernel');
        $routeModuleName = 'ZikulaRoutesModule';
        $install = $this->container->get('core_installer.controller.ajaxinstall')->installModule($routeModuleName);
        if (!$install) {
            // error
            return false;
        }

        // regenerate modules list
        $modApi = new \Zikula\ExtensionsModule\Api\AdminApi($kernel->getContainer(), new \Zikula\ExtensionsModule\ZikulaExtensionsModule());
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', ['filemodules' => $modApi->getfilemodules()]);

        // determine module id
        $mid = \ModUtil::getIdFromName($routeModuleName);

        // force load the modules admin API
        \ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);

        // set module to active
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', ['id' => $mid, 'state' => \ModUtil::STATE_INACTIVE]);
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', ['id' => $mid, 'state' => \ModUtil::STATE_ACTIVE]);

        // add the Routes module to the appropriate category
        $categories = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        $modscat = [];
        foreach ($categories as $category) {
            $modscat[$category['name']] = $category['cid'];
        }
        $category = __('System');
        $destinationCategoryId = isset($modscat[$category]) ? $modscat[$category] : \ModUtil::getVar('ZikulaAdminModule', 'defaultcategory');
        \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', ['module' => $routeModuleName, 'category' => (int)$destinationCategoryId]);

        return true;
    }

    private function upgradeModules()
    {
        // force load the modules admin API
        \ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);
        // this also regenerates all the modules
        return \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgradeall');
        // returns [[modname => boolean]]
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

    private function from140to141()
    {
        if (version_compare($this->currentVersion, '1.4.1', '>=')) {
            return true;
        }
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

        // take whatever additional actions necessary to upgrade from 140 to 141
        return true;
    }

    private function from141to142()
    {
        if (version_compare($this->currentVersion, '1.4.2', '>=')) {
            return true;
        }
        // do some clean up
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (isset($request) && $request->hasSession()) {
            $request->getSession()->remove('interactive_init');
            $request->getSession()->remove('interactive_remove');
            $request->getSession()->remove('interactive_upgrade');
        }

        return true;
    }

    private function from142to143()
    {
        if (version_compare($this->currentVersion, '1.4.3', '>=')) {
            return true;
        }
        // install ZAuth
        $kernel = $this->container->get('kernel');
        $zAuthModuleName = 'ZikulaZAuthModule';
        $install = $this->container->get('core_installer.controller.ajaxinstall')->installModule($zAuthModuleName);
        if (!$install) {
            // error
            return false;
        }

        // regenerate modules list
        $modApi = new \Zikula\ExtensionsModule\Api\AdminApi($kernel->getContainer(), new \Zikula\ExtensionsModule\ZikulaExtensionsModule());
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', ['filemodules' => $modApi->getfilemodules()]);

        // determine module id
        $mid = \ModUtil::getIdFromName($zAuthModuleName);

        // force load the modules admin API
        \ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);

        // set module to active
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', ['id' => $mid, 'state' => \ModUtil::STATE_INACTIVE]);
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', ['id' => $mid, 'state' => \ModUtil::STATE_ACTIVE]);

        // add the module to the appropriate category
        $categories = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        $modscat = [];
        foreach ($categories as $category) {
            $modscat[$category['name']] = $category['cid'];
        }
        $category = __('Users');
        $destinationCategoryId = isset($modscat[$category]) ? $modscat[$category] : \ModUtil::getVar('ZikulaAdminModule', 'defaultcategory');
        \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', ['module' => $zAuthModuleName, 'category' => (int)$destinationCategoryId]);

        return true;
    }

    private function finalizeParameters()
    {
        \ModUtil::initCoreVars(true); // initialize the modvars array (includes ZConfig (System) vars)
        // Set the System Identifier as a unique string.
        if (!\System::getVar('system_identifier')) {
            \System::setVar('system_identifier', str_replace('.', '', uniqid(rand(1000000000, 9999999999), true)));
        }

        // add new configuration parameters
        $params = $this->yamlManager->getParameters();
        unset($params['username'], $params['password']);
        if (!isset($params['secret']) || ($params['secret'] == 'ThisTokenIsNotSoSecretChangeIt')) {
            $params['secret'] = \RandomUtil::getRandomString(50);
        }
        if (!isset($params['url_secret'])) {
            $params['url_secret'] = \RandomUtil::getRandomString(10);
        }
        // Configure the Request Context
        // see http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally
        $params['router.request_context.host'] = isset($params['router.request_context.host']) ? $params['router.request_context.host'] : $this->container->get('request')->getHost();
        $params['router.request_context.scheme'] = isset($params['router.request_context.scheme']) ? $params['router.request_context.scheme'] : 'http';
        $params['router.request_context.base_url'] = isset($params['router.request_context.base_url']) ? $params['router.request_context.base_url'] : $this->container->get('request')->getBasePath();

        // set currently installed version into parameters
        $params[\Zikula_Core::CORE_INSTALLED_VERSION_PARAM] = \Zikula_Core::VERSION_NUM;

        $this->yamlManager->setParameters($params);

        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        \System::setVar('Version_Num', \Zikula_Core::VERSION_NUM);
        \System::setVar('Version_ID', \Zikula_Core::VERSION_ID);
        \System::setVar('Version_Sub', \Zikula_Core::VERSION_SUB);
        \System::setVar('language_i18n', \ZLanguage::getLanguageCode());

        // set the 'start' page information to empty to avoid missing module errors.
        \System::setVar('startpage', '');
        \System::setVar('starttype', '');
        \System::setVar('startfunc', '');
        \System::setVar('startargs', '');

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
