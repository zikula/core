<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Helper\Legacy;

use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;

/**
 * @deprecated remove at Core-2.0
 * Class ExtensionHelper
 */
class ExtensionHelper
{
    public static function upgrade(ExtensionEntity $extension)
    {
        $serviceManager = \ServiceUtil::getManager();
        $osdir = \DataUtil::formatForOS($extension->getDirectory());
        \ModUtil::dbInfoLoad($extension->getName(), $osdir);

        // add autoloaders for 1.3-type modules
        if ((false === strpos($osdir, '/')) && (is_dir("modules/$osdir/lib"))) {
            \ZLoader::addAutoloader($osdir, array("modules", "modules/$osdir/lib"));
        }
        $bootstrap = "modules/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
        $installer = self::getInstaller($extension->getName());
        // perform the actual upgrade of the module
        $func = array($installer, 'upgrade');

        if (is_callable($func)) {
            $result = call_user_func($func, $extension->getVersion());
            if (is_string($result)) {
                if ($result != $extension->getVersion()) {
                    // update the last successful updated version
                    $extension->setVersion($result);
                    $serviceManager->get('doctrine.entitymanager')->flush();
                }

                return false;
            } elseif ($result != true) {
                return false;
            }
        }
        $modversion = ExtensionsUtil::getVersionMeta($extension->getName());

        // Update state of module
        $serviceManager->get('zikula_extensions_module.extension_state_helper')->updateState($extension->getId(), \ModUtil::STATE_ACTIVE);

        // update the module with the new version
        $extension->setVersion($modversion['version']);
        $serviceManager->get('doctrine.entitymanager')->flush();

        // clear the cache before calling events
        $theme = \Zikula_View_Theme::getInstance();
        $theme->clear_compiled();
        $theme->clear_all_cache();
        $theme->clear_cssjscombinecache();
        $serviceManager->get('zikula.cache_clearer')->clear('symfony');

        if (!\System::isInstalling()) {
            // Upgrade succeeded, issue event.
            $event = new GenericEvent(null, $extension->toArray());
            $serviceManager->get('event_dispatcher')->dispatch('installer.module.upgraded', $event);

            $event = new ModuleStateEvent(null, $extension->toArray());
            $serviceManager->get('event_dispatcher')->dispatch(CoreEvents::MODULE_UPGRADE, $event);
        }

        return true;
    }

    public static function uninstall(ExtensionEntity $extension)
    {
        if ($extension->getState() == ExtensionApi::STATE_NOTALLOWED
            || ($extension->getType() == \ModUtil::TYPE_SYSTEM && $extension->getName() != 'ZikulaPageLockModule')) {
            throw new \RuntimeException(__f('Error! No permission to upgrade %s.', ['%s' => $extension->getDisplayname()]));
        }
        if ($extension->getState() == ExtensionApi::STATE_UNINITIALISED) {
            throw new \RuntimeException(__f('Error! %s is not yet installed, therefore it cannot be uninstalled.', ['%s' => $extension->getDisplayname()]));
        }

        $serviceManager = \ServiceUtil::getManager();
        $osdir = \DataUtil::formatForOS($extension->getDirectory());
        $oomod = \ModUtil::isOO($extension->getName());

        // add autoloaders for 1.3-type modules
        if ($oomod && (false === strpos($osdir, '/')) && (is_dir("modules/$osdir/lib"))) {
            \ZLoader::addAutoloader($osdir, array('modules', "modules/$osdir/lib"));
        }
        $module = \ModUtil::getModule($extension->getName(), true);
        $bootstrap = "modules/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        // Get module database info
        \ModUtil::dbInfoLoad($extension->getName(), $osdir);

        // perform the actual deletion of the module
        $installer = self::getInstaller($extension->getName());
        $func = array($installer, 'uninstall');
        if (is_callable($func)) {
            if (call_user_func($func) != true) {
                return false;
            }
        }

        // Delete any module variables that the module cleanup function might have missed
        $serviceManager->get('zikula_extensions_module.api.variable')->delAll($extension->getName());

        $version = ExtensionsUtil::getVersionMeta($extension->getName(), 'modules', $module);
        if (is_object($version)) {
            \HookUtil::unregisterProviderBundles($version->getHookProviderBundles());
            \HookUtil::unregisterSubscriberBundles($version->getHookSubscriberBundles());
            \EventUtil::unregisterPersistentModuleHandlers($extension->getName());
        }

        // remove the entry from the modules table
        $serviceManager->get('doctrine')->getManager()->getRepository('ZikulaExtensionsModule:ExtensionEntity')->removeAndFlush($extension);

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $serviceManager->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        $event = new GenericEvent(null, $extension->toArray());
        $serviceManager->get('event_dispatcher')->dispatch('installer.module.uninstalled', $event);

        $event = new ModuleStateEvent($module, ($module === null) ? $extension->toArray() : null);
        $serviceManager->get('event_dispatcher')->dispatch(CoreEvents::MODULE_REMOVE, $event);

        return true;
    }

    /**
     * get legacy installer object
     * @param $name
     * @return \Zikula_AbstractInstaller
     */
    private static function getInstaller($name)
    {
        $className = ucwords($name) . '\\' . ucwords($name) . 'Installer';
        $classNameOld = ucwords($name) . '_Installer';
        $className = class_exists($className) ? $className : $classNameOld;
        $reflectionInstaller = new \ReflectionClass($className);
        if ($reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
            $serviceManager = \ServiceUtil::getManager();
            return $reflectionInstaller->newInstanceArgs([$serviceManager]);
        } else {
            throw new \RuntimeException(__f("%s must be an instance of Zikula_AbstractInstaller.", $className));
        }
    }
}
