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

namespace Zikula\Module\ThemeModule;

use ModUtil;
use FileUtil;
use LogUtil;
use ThemeUtil;
use ServiceUtil;
use Zikula\Module\ThemeModule\Entity\ThemeEntity;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use ZLoader;

/**
 * Helper functions for the theme module
 */
class Util
{
    /**
     * Regenerates the theme list
     *
     * @return bool true
     */
    public static function regenerate()
    {
        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $helper = new \Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper($boot->getConnection(ServiceUtil::getManager()->get('kernel')));

        $helper->load();

        // Get all themes on filesystem
        $filethemes = array();

        $scanner = new Scanner();
        $scanner->scan(array('themes'), 4);
        $newThemes = $scanner->getThemesMetaData();

        foreach ($newThemes as $name => $theme) {
            foreach ($theme->getPsr0() as $ns => $path) {
                ZLoader::addPrefix($ns, $path);
            }
            $class = $theme->getClass();

            /** @var $bundle \Zikula\Core\AbstractTheme */
            $bundle = new $class;
            $class = $bundle->getVersionClass();

            $version = new $class($bundle);
            $version['name'] = $bundle->getName();

            $array = $version->toArray();
            unset($array['id']);

            $directory = explode('/', $bundle->getRelativePath());
            array_shift($directory);
            $array['directory'] = implode('/', $directory);

            $array['type'] = 3;
            $array['state'] = 1;
            $array['contact'] = 3;
            $array['xhtml'] = 1;
            $filethemes[$bundle->getName()] = $array;
        }

        $dirArray = FileUtil::getFiles('themes', false, true, null, 'd');
        foreach ($dirArray as $dir) {
            // Work out the theme type
            if (file_exists("themes/$dir/version.php")) {
                $themetype = 3;
                include "themes/$dir/version.php";
            } else {
                // anything else isn't a theme
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

        $sm = ServiceUtil::getManager();
        $entityManager = $sm->get('doctrine.entitymanager');

        $dbthemes = array();
        $themeEntities = $entityManager->getRepository('ZikulaThemeModule:ThemeEntity')->findAll();

        foreach ($themeEntities as $entity) {
            $entity = $entity->toArray();
            $dbthemes[$entity['name']] = $entity;
        }

        // See if we have lost any themes since last generation
        foreach ($dbthemes as $name => $themeinfo) {
            if (empty($filethemes[$name])) {
                // delete a running configuration
                ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleterunningconfig', array('themename' => $name));

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
                $item = new ThemeEntity;
                $item->merge($themeinfo);
                $entityManager->persist($item);
            }
        }

        // see if any themes have changed
        foreach ($filethemes as $name => $themeinfo) {
            if (isset($dbthemes[$name])) {
                if (($themeinfo['directory'] != $dbthemes[$name]['directory']) ||
                        ($themeinfo['type'] != $dbthemes[$name]['type']) ||
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
