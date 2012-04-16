<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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

namespace Theme;

use FileUtil, CacheUtil, ModUtil, ServiceUtil, LogUtil, ThemeUtil;

/**
 * Theme_Util class.
 */
class Util
{
    /**
     * Regenerates the theme list.
     */
    public static function regenerate()
    {
        // Get all themes on filesystem
        $filethemes = array();

        if (is_dir('themes')) {
            $dirArray = FileUtil::getFiles('themes', false, true, null, 'd');
            foreach ($dirArray as $dir) {
                // Work out the theme type
                if (file_exists("themes/$dir/version.php")) {
                    $themetype = 3;
                } else {
                    // anything else isn't a theme
                    continue;
                }

                // Get some defaults in case we don't have a theme version file
                $themeversion['name'] = preg_replace('/_/', ' ', $dir);
                $themeversion['displayname'] = preg_replace('/_/', ' ', $dir);
                $themeversion['version'] = '0';
                $themeversion['description'] = '';

                // include the correct version file based on theme type and
                // manipulate the theme version information
                if (file_exists($file = "themes/$dir/version.php")) {
                    if (!include($file)) {
                        LogUtil::registerError(__f('Error! Could not include theme version file: %s', $file));
                    }
                }

                $filethemes[$themeversion['name']] = array(
                        'directory' => $dir,
                        'name' => $themeversion['name'],
                        'type' => $themetype,
                        'displayname' => (isset($themeversion['displayname']) ? $themeversion['displayname'] : $themeversion['name']),
                        'version' => (isset($themeversion['version']) ? $themeversion['version'] : '1.0'),
                        'description' => (isset($themeversion['description']) ? $themeversion['description'] : $themeversion['displayname']),
                        'admin' => (isset($themeversion['admin']) ? (int)$themeversion['admin'] : '0'),
                        'user' => (isset($themeversion['user']) ? (int)$themeversion['user'] : '1'),
                        'system' => (isset($themeversion['system']) ? (int)$themeversion['system'] : '0'),
                        'state' => (isset($themeversion['state']) ? $themeversion['state'] : ThemeUtil::STATE_ACTIVE),
                        'contact' => (isset($themeversion['contact']) ? $themeversion['contact'] : ''),
                        'xhtml' => (isset($themeversion['xhtml']) ? (int)$themeversion['xhtml'] : 1));

                // important: unset themeversion otherwise all following themes will have
                // at least the same regid or other values not defined in
                // the next version.php files to be read
                unset($themeversion);
                unset($themetype);
            }
        }

        // get entityManager
        $sm = ServiceUtil::getManager();
        $entityManager = $sm->get('doctrine')->getEntityManager();

        // Get all themes in DB
        $dbthemes = array();
        $themes = $entityManager->getRepository('Theme\Entity\Theme')->findAll();

        foreach ($themes as $theme) {
            $theme = $theme->toArray();
            $dbthemes[$theme['directory']] = $theme;
        }

        // See if we have lost any themes since last generation
        foreach ($dbthemes as $name => $themeinfo) {
            if (empty($filethemes[$name])) {
                // delete a running configuration
                ModUtil::apiFunc('Theme', 'admin', 'deleterunningconfig', array('themename' => $name));

                // delete item from db
                $item = $entityManager->getRepository('Theme\Entity\Theme')->findOneBy(array('name' => $name));
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
                $item = new \Theme\Entity\Theme;
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
                    $item = $entityManager->getRepository('Theme\Entity\Theme')->find($themeinfo['id']);
                    $item->merge($themeinfo);
                }
            }
        }

        $entityManager->flush();

        return true;
    }
}
