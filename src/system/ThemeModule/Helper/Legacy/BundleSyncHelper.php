<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Helper\Legacy;

use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

/**
 * @deprecated remove at Core-2.0
 * Legacy helper functions for the theme module
 */
class BundleSyncHelper
{
    /**
     * scan for old theme types (<Core-1.4)
     * @return array
     * @throws \Exception
     */
    public static function scan()
    {
        $filethemes = [];
        $dirArray = \FileUtil::getFiles('themes', false, true, null, 'd');
        foreach ($dirArray as $dir) {
            // Work out the theme type
            if (file_exists("themes/$dir/version.php")) {
                // set defaults
                $themeversion = [];
                $themeversion['name'] = preg_replace('/_/', ' ', $dir);
                $themeversion['displayname'] = preg_replace('/_/', ' ', $dir);
                $themeversion['version'] = '0';
                $themeversion['description'] = '';
                include "themes/$dir/version.php"; // sets values for the variable $themeversion

                $filethemes[$themeversion['name']] = [
                    'directory' => $dir,
                    'name' => $themeversion['name'],
                    'type' => 3,
                    'displayname' => (isset($themeversion['displayname']) ? $themeversion['displayname'] : $themeversion['name']),
                    'version' => (isset($themeversion['version']) ? $themeversion['version'] : '1.0.0'),
                    'description' => (isset($themeversion['description']) ? $themeversion['description'] : $themeversion['displayname']),
                    'admin' => (isset($themeversion['admin']) ? (int)$themeversion['admin'] : '0'),
                    'user' => (isset($themeversion['user']) ? (int)$themeversion['user'] : '1'),
                    'system' => (isset($themeversion['system']) ? (int)$themeversion['system'] : '0'),
                    'state' => (isset($themeversion['state']) ? $themeversion['state'] : ThemeEntityRepository::STATE_INACTIVE),
                    'contact' => (isset($themeversion['contact']) ? $themeversion['contact'] : ''),
                    'xhtml' => (isset($themeversion['xhtml']) ? (int)$themeversion['xhtml'] : 1)
                ];
            }
        }

        return $filethemes;
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public static function deleteRunningConfig($name)
    {
        // delete a running configuration
        try {
            \ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleterunningconfig', ['themename' => $name]);
        } catch (\Exception $e) {
            if (\System::isInstalling()) {
                // silent fail when installing or upgrading
            } else {
                throw $e;
            }
        }
    }
}
