<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Module\ThemeModule\Util as ThemeUtil;
use Zikula\Bundle\CoreBundle\YamlDumper;

/**
 * Class AjaxUpgradeController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
class AjaxUpgradeController extends AbstractController
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function ajaxAction(Request $request)
    {
        $stage = $request->request->get('stage');
        $this->container->setParameter('upgrading', true);
        $status = $this->executeStage($stage);
        $response = array('status' => (boolean) $status);
        if (is_array($status)) {
            $response['results'] = $status;
        }

        return new JsonResponse($response);
    }

    public function commandLineAction($stage)
    {
        return $this->executeStage($stage);
    }

    private function executeStage($stageName)
    {
        switch($stageName) {
            case "loginadmin":
                $this->yamlManager->setParameter('upgrading', true);
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
        $kernel = $this->container->get('kernel');
        $routeModuleName = 'ZikulaRoutesModule';
        $install = $this->container->get('core_installer.controller.ajaxinstall')->installModule($routeModuleName);
        if (!$install) {
            // error
            return false;
        }

        // regenerate modules list
        $modApi = new \Zikula\Module\ExtensionsModule\Api\AdminApi($kernel->getContainer(), new \Zikula\Module\ExtensionsModule\ZikulaExtensionsModule());
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', array('filemodules' => $modApi->getfilemodules()));

        // determine module id
        $mid = \ModUtil::getIdFromName($routeModuleName, true);

        // force load the modules admin API
        \ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);

        // set module to active
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', array('id' => $mid, 'state' => \ModUtil::STATE_INACTIVE));
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', array('id' => $mid, 'state' => \ModUtil::STATE_ACTIVE));

        // add the Routes module to the appropriate category
        $categories = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        $modscat = array();
        foreach ($categories as $category) {
            $modscat[$category['name']] = $category['cid'];
        }
        $category = __('System');
        $destinationCategoryId = isset($modscat[$category]) ? $modscat[$category] : \ModUtil::getVar('ZikulaAdminModule', 'defaultcategory');
        \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', array('module' => $routeModuleName, 'category' => (int)$destinationCategoryId));

        return true;
    }

    private function upgradeModules()
    {
        // force load the modules admin API
        \ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);
        return \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgradeall');
        // returns array(array(modname => boolean))
    }

    private function regenerateThemes()
    {
        // set default theme to Andreas08
        \System::setVar('Default_Theme', 'ZikulaAndreas08Theme');
        // regenerate the themes list
        return ThemeUtil::regenerate();
    }

    private function finalizeParameters()
    {
        \ModUtil::initCoreVars(true); // initialize the modvars array (includes ZConfig (System) vars)
        // Set the System Identifier as a unique string.
        if (!\System::getVar('system_identifier')) {
            \System::setVar('system_identifier', str_replace('.', '', uniqid(rand(1000000000, 9999999999), true)));
        }

        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        \System::setVar('Version_Num', \Zikula_Core::VERSION_NUM);
        \System::setVar('language_i18n', \ZLanguage::getLanguageCode());

        // add new configuration parameters
        $params = $this->yamlManager->getParameters();
        unset($params['username'], $params['password']);
        $params['secret'] = \RandomUtil::getRandomString(50);
        $params['url_secret'] = \RandomUtil::getRandomString(10);
        // Configure the Request Context
        // see http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally
        $params['router.request_context.host'] = isset($params['router.request_context.host']) ? $params['router.request_context.host'] :$this->container->get('request')->getHost();
        $params['router.request_context.scheme'] = isset($params['router.request_context.scheme']) ? $params['router.request_context.scheme'] : 'http';
        $params['router.request_context.base_url'] = isset($params['router.request_context.base_url']) ? $params['router.request_context.base_url'] : $this->container->get('request')->getBasePath();
        $this->yamlManager->setParameters($params);

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
        if (in_array($this->container->getParameter('env'), array('dev', 'prod'))) {
            // this is just in case anyone ever creates a mode that isn't dev|prod
            $this->container->get('cache_clearer')->clear($this->container->getParameter('env'));
        }

        // finally remove upgrading flag in parameters
        $this->yamlManager->delParameter('upgrading');

        return true;
    }
}
