<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        if (version_compare(\Zikula_Core::VERSION_NUM, '1.4.0', '>') && version_compare(ZIKULACORE_CURRENT_INSTALLED_VERSION, '1.4.0', '>=')) {
            // this stage is not necessary to upgrade from 1.4.0 -> 1.4.x
            return false;
        }
        $this->init();
        $this->upgradeUsersModule();

        return false;
    }

    public function getTemplateParams()
    {
        return array();
    }

    private function init()
    {
        $conn = $this->container->get('doctrine.dbal.default_connection');
        /** @var \ZikulaKernel $kernel */
        $kernel = $this->container->get('kernel');

        $res = $conn->executeQuery("SELECT name FROM modules WHERE name = 'ZikulaExtensionsModule'");
        $result = $res->fetch();
        if ($result) {
            // nothing to do, already converted.
            return '';
        }

        // remove event handlers that were replaced by DependencyInjection
        $conn->executeQuery("DELETE FROM module_vars WHERE modname = '/EventHandlers' AND name IN ('Extensions', 'Users', 'Search', 'Settings')");

        // remove old Errors module from modules table (uninstall and delete)
        $conn->executeQuery("DELETE FROM modules WHERE name = 'Errors'");

        // rename modules in tables: modules, module_vars, group_perms
        $oldModuleNames = array(
            'Admin', 'Blocks', 'Categories', 'Extensions', 'Groups',
            'Mailer', 'PageLock', 'Permissions', 'Search', 'SecurityCenter',
            'Settings', 'Theme', 'Users',
        );
        foreach ($oldModuleNames as $module) {
            $conn->executeQuery("UPDATE modules SET name = 'Zikula{$module}Module', directory = '{$module}Module' WHERE name = '$module'");
            $conn->executeQuery("UPDATE module_vars SET modname = 'Zikula{$module}Module' WHERE modname = '$module'");
            $strlen = strlen($module) + 1;
            $conn->executeQuery("UPDATE group_perms SET component = CONCAT('Zikula{$module}Module', SUBSTRING(component, $strlen)) WHERE component LIKE '{$module}%'");
        }

        // rename themes in tables: themes
        $oldThemeNames = array(
            'Andreas08', 'Atom', 'SeaBreeze', 'Mobile', 'Printer',
        );
        foreach ($oldThemeNames as $theme) {
            $conn->executeQuery("UPDATE themes SET name = 'Zikula{$theme}Theme', directory = '{$theme}Theme' WHERE name = '$theme'");
        }
        $conn->executeQuery("UPDATE themes SET name = 'ZikulaRssTheme', directory = 'RssTheme' WHERE name = 'RSS'");

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
        $conn->executeQuery("UPDATE module_vars SET value = 's:20:\"ZikulaAndreas08Theme\";' WHERE modname = 'ZConfig' AND name = 'Default_Theme'");

        // confirm custom module urls are valid with new routes, reset if not
        $modules = $conn->fetchAll("SELECT * FROM modules");
        foreach ($modules as $module) {
            $path = realpath($kernel->getRootDir() . '/../' . $module['url']);
            if (is_dir($path)) {
                $meta = \Zikula\ExtensionsModule\Util::getVersionMeta($module['name']);
                $conn->executeQuery("UPDATE modules SET url = '$meta[url]' WHERE id = $modules[id]");
            }
        }

        // ensure data in modules:capabilities is valid
        $conn->executeQuery("UPDATE `modules` SET `capabilities`='a:0:{}' WHERE `capabilities`=''");

        // install Bundles table
        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $helper = new \Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper($boot->getConnection($kernel));
        $helper->createSchema();
        $helper->load();
        $bundles = array();
        // this neatly autoloads
        $boot->getPersistedBundles($kernel, $bundles);
    }

    private function upgradeUsersModule()
    {
        $oldModuleInfo = \ModUtil::getInfoFromName('ZikulaUsersModule');
        /** @var \Zikula\Core\AbstractBundle $module */
        $module = $this->container->get('kernel')->getModule('ZikulaUsersModule');
        $installerInstance = new \Zikula\UsersModule\UsersModuleInstaller($this->container, $module);
        $installerInstance->upgrade($oldModuleInfo['version']);
        $versionInstance = new \Zikula\UsersModule\UsersModuleVersion($module);
        $metaData = $versionInstance->getMetaData();
        $item = $this->container->get('doctrine.entitymanager')->getRepository(\Zikula\ExtensionsModule\Api\AdminApi::EXTENSION_ENTITY)->find($oldModuleInfo['id']);
        $item['version'] = $metaData['version'];
        $item['state'] = \ModUtil::STATE_ACTIVE;
        $this->container->get('doctrine.entitymanager')->flush();
    }
}
