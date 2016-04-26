<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Api;

use CacheUtil;
use DataUtil;
use ModUtil;
use SecurityUtil;
use ThemeUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @deprecated remove at Core-2.0
 * API functions used by administrative controllers
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Regenerate themes list.
     *
     * @deprecated since 1.4.0 use Util::regenerate instead
     *
     * @return boolean
     */
    public function regenerate()
    {
        return $this->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();
    }

    /**
     * update theme settings
     *
     * @param array[] $args {
     *      @type array $themeinfo new theme information to update
     *                      }
     *
     * @return bool true if successful
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws \InvalidArgumentException Thrown if the themeinfo parameter isn't provided
     */
    public function updatesettings($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Check our input arguments
        if (!isset($args['themeinfo'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        unset($args['themeinfo']['i18n']);

        $item = $this->entityManager->find('ZikulaThemeModule:ThemeEntity', $args['themeinfo']['id']);
        $item->merge($args['themeinfo']);
        $this->entityManager->flush();

        return true;
    }

    /**
     * create running configuration
     *
     * @param string[] $args {
     *      @type $themename string the name of the theme to create a running configuration for
     *                       }
     *
     * @return bool true if successful
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws \InvalidArgumentException Thrown if the themename parameter isn't provided or
     *                                          if the requested theme version file cannot be found
     */
    public function createrunningconfig($args)
    {
        // check our input
        if (!isset($args['themename']) || empty($args['themename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $themename = $args['themename'];
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));
        if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get the theme settings and write them back to the running config directory
        $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getvariables', ['theme' => $themename]);
        if (is_array($variables)) {
            ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', [
                'theme' => $themename,
                'assoc_arr' => $variables,
                'has_sections' => true,
                'file' => 'themevariables.ini'
            ]);
        }

        // get the theme palettes and write them back to the running config directory
        $palettes = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettes', ['theme' => $themename]);
        if (is_array($palettes)) {
            ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', [
                'theme' => $themename,
                'assoc_arr' => $palettes,
                'has_sections' => true,
                'file' => 'themepalettes.ini'
            ]);
        }

        // get the theme page configurations and write them back to the running config directory
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', ['theme' => $themename]);
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', [
            'theme' => $themename,
            'assoc_arr' => $pageconfigurations,
            'has_sections' => true,
            'file' => 'pageconfigurations.ini'
        ]);

        foreach ($pageconfigurations as $pageconfiguration) {
            $fullpageconfiguration = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', [
                'theme' => $themename,
                'filename' => $pageconfiguration['file']
            ]);
            ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', [
                'theme' => $themename,
                'assoc_arr' => $fullpageconfiguration,
                'has_sections' => true,
                'file' => $pageconfiguration['file']
            ]);
        }

        return true;
    }

    /**
     * delete a running configuration
     *
     * @param string[] $args {
     *      @type $themename the name of the theme of which to the delete the running configuration
     *                       }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if the themename parameter isn't provided
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the theme
     */
    public function deleterunningconfig($args)
    {
        // check our input
        if (!isset($args['themename']) || empty($args['themename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $themename = $args['themename'];
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // define the base files
        $files = ['pageconfigurations.ini', 'themepalettes.ini', 'themevariables.ini'];

        // get the theme info to identify further files to delete
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if ($themeinfo) {
            $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', ['theme' => $themename]);
            if (is_array($pageconfigurations)) {
                foreach ($pageconfigurations as $pageconfiguration) {
                    $files[] = $pageconfiguration['file'];
                }
            }
        }

        // delete each file
        foreach ($files as $file) {
            ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleteinifile', ['file' => $file, 'themename' => $themename]);
        }

        return true;
    }

    /**
     * delete ini file
     *
     * @param string[] $args {
     *      @type string $file the name of the ini file to delete
     *      @type $themename the name of the theme to which the ini file belongs
     *                       }
     *
     * @return void
     *
     * @throws \InvalidArgumentException Thrown if either the themename or file parameter isn't provided
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the theme
     */
    public function deleteinifile($args)
    {
        if (!isset($args['themename']) || empty($args['themename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $themename = $args['themename'];
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!isset($args['file']) || empty($args['file'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $ostemp  = CacheUtil::getLocalDir();
        $ostheme = DataUtil::formatForOS($themename);
        $osfile  = $ostemp.'/Theme_Config/'.$ostheme.'/'.DataUtil::formatForOS($args['file']);

        if (file_exists($osfile) && is_writable($osfile)) {
            unlink($osfile);
        }
    }

    /**
     * delete a page configuration assignment
     *
     * @param string[] $args {
     *      @type string $pcname the name of the page configuration
     *      @type string $themename the name of the theme the page configuration belongs to
     *                       }
     *
     * @return bool true if successful, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if either the themename or pcname parameters aren't provided
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the theme
     */
    public function deletepageconfigurationassignment($args)
    {
        // Argument check
        if (!isset($args['themename']) && !isset($args['pcname'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $themeid = ThemeUtil::getIDFromName($args['themename']);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo($themeid);

        if ($themeinfo == false) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themeinfo[name]::pageconfigurations", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // read the list of existing page config assignments
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', ['theme' => $args['themename']]);

        // remove the requested page configuration
        unset($pageconfigurations[$args['pcname']]);

        // write the page configurations back to the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', [
            'theme' => $args['themename'],
            'assoc_arr' => $pageconfigurations,
            'has_sections' => true,
            'file' => 'pageconfigurations.ini'
        ]);

        return true;
    }
}
