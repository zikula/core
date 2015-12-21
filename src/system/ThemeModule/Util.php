<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ThemeModule;

use ModUtil;
use FileUtil;
use ZLanguage;
use ThemeUtil;
use ServiceUtil;
use Zikula\ThemeModule\Entity\ThemeEntity;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use ZLoader;
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap;
use Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper;

/**
 * Helper functions for the theme module
 */
class Util
{
    /**
     * Regenerates the theme list
     * @return bool true
     * @throws \Exception
     */
    public static function regenerate()
    {
        $sm = ServiceUtil::getManager();
        $boot = new Bootstrap();
        $helper = new BootstrapHelper($boot->getConnection($sm->get('kernel')));

        // sync the filesystem and the bundles table
        $helper->load();

        // Get all themes on filesystem
        $filethemes = array();

        $scanner = new Scanner();
        $scanner->scan(array('themes'), 4);
        $newThemes = $scanner->getThemesMetaData();

        /** @var \Zikula\Bundle\CoreBundle\Bundle\MetaData $themeMetaData */
        foreach ($newThemes as $name => $themeMetaData) {
            // PSR-0 is @deprecated - remove in Core-2.0
            foreach ($themeMetaData->getPsr0() as $ns => $path) {
                ZLoader::addPrefix($ns, $path);
            }
            foreach ($themeMetaData->getPsr4() as $ns => $path) {
                ZLoader::addPrefixPsr4($ns, $path);
            }

            $bundleClass = $themeMetaData->getClass();

            /** @var $bundle \Zikula\Core\AbstractTheme */
            $bundle = new $bundleClass();
            $versionClass = $bundle->getVersionClass();

            if (class_exists($versionClass)) {
                // 1.4-module spec - deprecated - remove in Core-2.0
                $version = new $versionClass($bundle);
                $version['name'] = $bundle->getName();

                $themeVersionArray = $version->toArray();
                unset($themeVersionArray['id']);
                $themeVersionArray['xhtml'] = 1;
            } else {
                // 2.0-module spec
                $themeMetaData->setTranslator(\ServiceUtil::get('translator'));
                $themeMetaData->setDirectoryFromBundle($bundle);
                $themeVersionArray = $themeMetaData->getThemeFilteredVersionInfoArray();
            }

            $directory = explode('/', $bundle->getRelativePath());
            array_shift($directory);
            $themeVersionArray['directory'] = implode('/', $directory);

            // loads the gettext domain for theme
            ZLanguage::bindThemeDomain($bundle->getName());

            // set defaults for all themes
            $themeVersionArray['type'] = 3;
            $themeVersionArray['state'] = 1;
            $themeVersionArray['contact'] = 3;

            $filethemes[$bundle->getName()] = $themeVersionArray;
        }

        // scan for old theme types (<Core-1.4) @deprecated - remove at 2.0
        $dirArray = FileUtil::getFiles('themes', false, true, null, 'd');
        foreach ($dirArray as $dir) {
            // Work out the theme type
            if (file_exists("themes/$dir/version.php")) {
                $themetype = 3;
                // set defaults
                $themeversion['name'] = preg_replace('/_/', ' ', $dir);
                $themeversion['displayname'] = preg_replace('/_/', ' ', $dir);
                $themeversion['version'] = '0';
                $themeversion['description'] = '';
                include "themes/$dir/version.php";
            } else {
                // anything else isn't a theme
                // this skips all directories not containing a version.php file (including >=1.4-type themes)
                continue;
            }

            $filethemes[$themeversion['name']] = array('directory' => $dir,
                    'name' => $themeversion['name'],
                    'type' => 3,
                    'displayname' => (isset($themeversion['displayname']) ? $themeversion['displayname'] : $themeversion['name']),
                    'version' => (isset($themeversion['version']) ? $themeversion['version'] : '1.0.0'),
                    'description' => (isset($themeversion['description']) ? $themeversion['description'] : $themeversion['displayname']),
                    'admin' => (isset($themeversion['admin']) ? (int)$themeversion['admin'] : '0'),
                    'user' => (isset($themeversion['user']) ? (int)$themeversion['user'] : '1'),
                    'system' => (isset($themeversion['system']) ? (int)$themeversion['system'] : '0'),
                    'state' => (isset($themeversion['state']) ? $themeversion['state'] : ThemeUtil::STATE_ACTIVE),
                    'contact' => (isset($themeversion['contact']) ? $themeversion['contact'] : ''),
                    'xhtml' => (isset($themeversion['xhtml']) ? (int)$themeversion['xhtml'] : 1));

            unset($themeversion);
            unset($themetype);
        }

        /****
         * Persist themes
         */
        $entityManager = $sm->get('doctrine.entitymanager');

        $dbthemes = array();
        $themeEntities = $entityManager->getRepository('ZikulaThemeModule:ThemeEntity')->findAll();

        // @todo - can this be done with the `findAll()` method or doctrine (index by name, hydrate to array?)
        foreach ($themeEntities as $entity) {
            $entity = $entity->toArray();
            $dbthemes[$entity['name']] = $entity;
        }

        // See if we have lost any themes since last generation
        foreach ($dbthemes as $name => $themeinfo) {
            if (empty($filethemes[$name])) {
                // delete a running configuration
                try {
                    ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleterunningconfig', array('themename' => $name));
                } catch (\Exception $e) {
                    if (\System::isInstalling()) {
                        // silent fail when installing or upgrading
                    } else {
                        throw $e;
                    }
                }

                // delete item from db
                $item = $entityManager->getRepository('ZikulaThemeModule:ThemeEntity')->findOneBy(array('name' => $name));
                $entityManager->remove($item);

                unset($dbthemes[$name]);
            }
        }

        // See if we have gained any themes since last generation,
        // or if any current themes have been upgraded
        foreach ($filethemes as $name => $themeinfo) {
            if (empty($dbthemes[$name])) {
                // new theme
                $themeinfo['state'] = ThemeUtil::STATE_ACTIVE;

                // add item to db
                $item = new ThemeEntity();
                $item->merge($themeinfo);
                $entityManager->persist($item);
            }
        }

        // see if any themes have changed
        foreach ($filethemes as $name => $themeinfo) {
            if (isset($dbthemes[$name])) {
                if (($themeinfo['directory'] != $dbthemes[$name]['directory']) ||
                    ($themeinfo['type'] != $dbthemes[$name]['type']) ||
                    ($themeinfo['description'] != $dbthemes[$name]['description']) ||
                        ($themeinfo['version'] != $dbthemes[$name]['version']) ||
                        ($themeinfo['admin'] != $dbthemes[$name]['admin']) ||
                        ($themeinfo['user'] != $dbthemes[$name]['user']) ||
                        ($themeinfo['system'] != $dbthemes[$name]['system']) ||
                        ($themeinfo['state'] != $dbthemes[$name]['state']) ||
                        ($themeinfo['contact'] != $dbthemes[$name]['contact']) ||
                        ($themeinfo['xhtml'] != $dbthemes[$name]['xhtml'])) {
                    $themeinfo['id'] = $dbthemes[$name]['id'];

                    // update item
                    /** @var $item ThemeEntity */
                    $item = $entityManager->getRepository('ZikulaThemeModule:ThemeEntity')->find($themeinfo['id']);
                    $item->merge($themeinfo);
                }
            }
        }

        $entityManager->flush();

        return true;
    }
}
