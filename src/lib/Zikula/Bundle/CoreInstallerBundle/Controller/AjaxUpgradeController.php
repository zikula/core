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
use Symfony\Component\Yaml\Yaml;

/**
 * Class AjaxUpgradeController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
 */
class AjaxUpgradeController extends AbstractController
{
    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function ajaxAction(Request $request)
    {
        $stage = $request->request->get('stage');
        $status = $this->executeStage($stage);

        return new JsonResponse(array('status' => $status));
    }

    private function executeStage($stageName)
    {
        switch($stageName) {
            case "upgrademodules":
                return $this->upgradeModules();
            case "reloadroutes":
                return $this->container->get('core_installer.controller.ajaxinstall')->reloadRoutes();
            case "regenthemes":
                return $this->regenerateThemes();
            case "loginadmin":
                return $this->container->get('core_installer.controller.ajaxinstall')->loginAdmin();
            case "finalizeparameters":
                return $this->finalizeParameters();
        }
        \System::setInstalling(false);
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
        // regenerate the themes list
       return  \ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'regenerate');
    }

    private function finalizeParameters()
    {
        $request = $this->container->get('request');

        // Set the System Identifier as a unique string.
        if (!\System::getVar('system_identifier')) {
            \System::setVar('system_identifier', str_replace('.', '', uniqid(rand(1000000000, 9999999999), true)));
        }

        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        \System::setVar('Version_Num', \Zikula_Core::VERSION_NUM);
        \System::setVar('language_i18n', \ZLanguage::getLanguageCode());

        // add new configuration parameters
        $rootDir = $this->container->get('kernel')->getRootDir() . "/config";
        $path = $rootDir . "/custom_parameters.yml";
        if (!is_readable($path)) {
            $path = $rootDir . "/parameters.yml";
        }
        $parameters = Yaml::parse(file_get_contents($path));
        $parameters['parameters']['secret'] = \RandomUtil::getRandomString(50);
        $parameters['parameters']['url_secret'] = \RandomUtil::getRandomString(10);
        // Configure the Request Context
        // see http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally
        $parameters['parameters']['router.request_context.host'] = $request->getHost();
        $parameters['parameters']['router.request_context.scheme'] = 'http';
        $parameters['parameters']['router.request_context.base_url'] = $request->getBasePath();

        file_put_contents($rootDir . "/custom_parameters.yml", Yaml::dump($parameters));

        \Zikula_View_Theme::getInstance()->clear_all_cache();
        \Zikula_View_Theme::getInstance()->clear_compiled();
        $cacheClearer = $this->container->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        return true;
    }
}
