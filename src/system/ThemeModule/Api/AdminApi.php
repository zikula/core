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

namespace Zikula\ThemeModule\Api;

use Zikula\ThemeModule\Util;
use ModUtil;
use SecurityUtil;
use LogUtil;
use System;
use ThemeUtil;
use DataUtil;
use FileUtil;
use CacheUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
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
        return Util::regenerate();
    }

    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulathememodule_admin_view'),
                'text' => __('Themes list'),
                'icon' => 'list');
        }
        if (SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulathememodule_admin_modifyconfig'),
                'text' => __('Settings'),
                'icon' => 'wrench');
        }

        return $links;
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
     * set default site theme
     *
     * @param mixed[] $args {
     *      @type string $themename         the name of the theme to set as the default for the site
     *      @type bool   $resetuserselected if true any existing user chosen themes will be reset to the site default
     *                      }
     *
     * @return bool true if successful, false otherwise
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws \InvalidArgumentException Thrown if the themename parameter isn't provided
     */
    public function setasdefault($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Check our input arguments
        if (!isset($args['themename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }
        if (!isset($args['resetuserselected'])) {
            $args['resetuserselected'] = false;
        }

        // if chosen reset all user theme selections
        if ($args['resetuserselected']) {
            $query = $this->entityManager->createQueryBuilder()
                                         ->update('ZikulaUsersModule:UserEntity', 'u')
                                         ->set('u.theme', ':null')
                                         ->setParameter('null', '')
                                         ->getQuery();
            $query->getResult();
        }

        // change default theme
        if (!System::setVar('Default_Theme', $args['themename'])) {
            return false;
        }

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
        $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getvariables', array('theme' => $themename));
        if (is_array($variables)) {
            ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => 'themevariables.ini'));
        }

        // get the theme palettes and write them back to the running config directory
        $palettes = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettes', array('theme' => $themename));
        if (is_array($palettes)) {
            ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $palettes, 'has_sections' => true, 'file' => 'themepalettes.ini'));
        }

        // get the theme page configurations and write them back to the running config directory
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', array('theme' => $themename));
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

        foreach ($pageconfigurations as $pageconfiguration) {
            $fullpageconfiguration = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $pageconfiguration['file']));
            ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $fullpageconfiguration, 'has_sections' => true, 'file' => $pageconfiguration['file']));
        }

        return true;
    }

    /**
     * Delete a theme.
     *
     * @param string[] $args {
     *      @type $themename string the name of the theme to delete
     *                       }
     *
     * @return bool true if successful, false otherwise
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to delete the theme
     * @throws \InvalidArgumentException Thrown if the themename parameter isn't provided
     * @throws \RuntimeException Thrown if the theme cannot be deleted
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['themename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $themeid = (int)ThemeUtil::getIDFromName($args['themename']);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo($themeid);

        if ($themeinfo == false) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themeinfo[name]::", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // reset the theme for any users utilising this theme.
        $query = $this->entityManager->createQueryBuilder()
                                     ->update('ZikulaUsersModule:UserEntity', 'u')
                                     ->set('u.theme', ':null')
                                     ->where('u.theme = :themeName')
                                     ->setParameter('null', '')
                                     ->setParameter('themeName', $themeinfo['name'])
                                     ->getQuery();

        $result = $query->getResult();
        if (!$result) {
            return false;
        }

        // delete theme
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaThemeModule:ThemeEntity', 't')
                                     ->where('t.id = :id')
                                     ->setParameter('id', $themeid)
                                     ->getQuery();

        $result = $query->getResult();
        if (!$result) {
            throw new \RuntimeException(__('Error! Could not perform the deletion.'));
        }

        // delete the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleterunningconfig', array('themename' => $themeinfo['name']));

        // clear the compiled and cached templates
        // Note: This actually clears ALL compiled and cached templates but there doesn't seem to be
        // a way to clear out only files associated with a theme without supplying all the template
        // names used by that theme.
        // see http://smarty.php.net/manual/en/api.clear.cache.php
        // and http://smarty.php.net/manual/en/api.clear.compiled.tpl.php
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'clear_compiled');
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'clear_cached');

        // try to delete the files
        if ($args['deletefiles'] == 1) {
            ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deletefiles', array('themename' => $themeinfo['name'], 'themedirectory' => $themeinfo['directory']));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * delete theme files from the file system if possible
     *
     * @param string[] $args {
     *      @type $themename string the name of the theme to remove from the file system
     *                       }
     *
     * @return bool true if successful, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either the themename or themedirectory parameters aren't provided
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws RuntimeException Thrown if the theme files cannot be deleted from the file system
     */
    public function deletefiles($args)
    {
        // check our input
        if (!isset($args['themename']) || empty($args['themename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $themename = $args['themename'];
        }

        if (!isset($args['themedirectory']) || empty($args['themedirectory'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $osthemedirectory = DataUtil::formatForOS($args['themedirectory']);
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', $themename .'::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (is_writable('themes') && is_writable('themes/' . $osthemedirectory)) {
            $res = FileUtil::deldir('themes/' .$osthemedirectory);
            if ($res == true) {
                return LogUtil::registerStatus(__('Done! Removed theme files from the file system.'));
            }

            throw new \RuntimeException(__('Error! Could not delete theme files from the file system. Please remove them by another means (FTP, SSH, ...).'));
        }

        LogUtil::registerStatus(__f('Notice: Theme files cannot be deleted because Zikula does not have write permissions for the themes folder and/or themes/%s folder.', DataUtil::formatForDisplay($args['themedirectory'])));

        return false;
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
        $files = array('pageconfigurations.ini', 'themepalettes.ini', 'themevariables.ini');

        // get the theme info to identify further files to delete
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if ($themeinfo) {
            $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', array('theme' => $themename));
            if (is_array($pageconfigurations)) {
                foreach ($pageconfigurations as $pageconfiguration) {
                    $files[] = $pageconfiguration['file'];
                }
            }
        }

        // delete each file
        foreach ($files as $file) {
            ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleteinifile', array('file' => $file, 'themename' => $themename));
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
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', array('theme' => $args['themename']));

        // remove the requested page configuration
        unset($pageconfigurations[$args['pcname']]);

        // write the page configurations back to the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $args['themename'], 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

        return true;
    }
}
