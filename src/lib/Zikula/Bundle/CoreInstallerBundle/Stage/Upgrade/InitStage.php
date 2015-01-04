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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class InitStage implements StageInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'init';
    }

    public function getTemplateName()
    {
        return "";
    }

    public function isNecessary()
    {
        $this->init();
        return false;
    }

    public function getTemplateParams()
    {
        return array();
    }

    private function init()
    {
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $kernel = $this->container->get('kernel');
        $request = $this->container->get('request');

        $res = $conn->executeQuery("SELECT name FROM modules WHERE name = 'ZikulaExtensionsModule'");
        if ($res->fetch()) {
            // nothing to do, already converted.
            return '';
        }

        // remove event handlers that were replaced by DependencyInjection
        $conn->executeQuery("DELETE FROM module_vars WHERE modname = '/EventHandlers' AND name IN ('Extensions', 'Users', 'Search', 'Settings')");

        // rename modules in tables: modules, module_vars, group_perms
        $oldModuleNames = array(
            'Admin', 'Blocks', 'Categories', 'Errors', 'Extensions', 'Groups',
            'Mailer', 'PageLock', 'Permissions', 'Search', 'SecurityCenter',
            'Settings', 'Theme', 'Users',
        );
        foreach ($oldModuleNames as $module) {
            $conn->executeQuery("UPDATE modules SET name = 'Zikula{$module}Module', directory = 'Zikula/Module/{$module}Module' WHERE name = '$module'");
            $conn->executeQuery("UPDATE module_vars SET modname = 'Zikula{$module}Module' WHERE modname = '$module'");
            $strlen = strlen($module) + 1;
            $conn->executeQuery("UPDATE group_perms SET component = CONCAT('Zikula{$module}Module', SUBSTRING(component, $strlen)) WHERE component LIKE '{$module}%'");
        }

        // rename themes in tables: themes
        $oldThemeNames = array(
            'Andreas08', 'Atom', 'SeaBreeze', 'Mobile', 'Printer',
        );
        foreach ($oldThemeNames as $theme) {
            $conn->executeQuery("UPDATE themes SET name = 'Zikula{$theme}Theme', directory = 'Zikula/Theme/{$theme}Theme' WHERE name = '$theme'");
        }
        $conn->executeQuery("UPDATE themes SET name = 'ZikulaRssTheme', directory = 'Zikula/Theme/RssTheme' WHERE name = 'RSS'");

        // update 'Users' -> 'ZikulaUsersModule' in all the hook tables
        $sqls = array();
        $sqls[] = "UPDATE hook_area SET owner = 'ZikulaUsersModule' WHERE owner = 'Users'";
        $sqls[] = "UPDATE hook_binding SET sowner = 'ZikulaUsersModule' WHERE sowner = 'Users'";
        $sqls[] = "UPDATE hook_runtime SET sowner = 'ZikulaUsersModule' WHERE sowner = 'Users'";
        $sqls[] = "UPDATE hook_subscriber SET owner = 'ZikulaUsersModule' WHERE owner = 'Users'";
        foreach ($sqls as $sql) {
            $conn->executeQuery($sql);
        }

        // update default theme name
        $conn->executeQuery("UPDATE module_vars SET value = 'ZikulaAndreas08Theme' WHERE modname = 'ZConfig' AND value='Default_Theme'");

        // install Bundles table
        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $helper = new \Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper($boot->getConnection($kernel));
        $helper->createSchema();
        $helper->load();
        $bundles = array();
        // this neatly autoloads
        $boot->getPersistedBundles($kernel, $bundles);

        // install the Routes module
        $this->installRoutesModule($kernel);
    }

    /**
     * Calls Routes module installer
     *
     * @param \ZikulaKernel $kernel
     *
     * @return boolean
     */
    private function installRoutesModule(\ZikulaKernel $kernel)
    {
        // manually install the Routes module
        $routeModuleName = 'ZikulaRoutesModule';
        $module = $kernel->getModule($routeModuleName);
        $installerClassName = $module->getInstallerClass();
        $bootstrap = $module->getPath() . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
        /** @var \Zikula_AbstractInstaller $instance */
        $instance = new $installerClassName($kernel->getContainer(), $module);
        if (!$instance->install()) {
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
        $destinationCategoryId = isset($modscat[$category]) ? $modscat[$category] : $modscat[0];
        \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', array('module' => $routeModuleName, 'category' => $destinationCategoryId));

        return true;
    }
}