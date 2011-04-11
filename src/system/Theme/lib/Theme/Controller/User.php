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

class Theme_Controller_User extends Zikula_AbstractController
{
    /**
     * display theme changing user interface
     */
    public function main()
    {
        // check if theme switching is allowed
        if (!System::getVar('theme_change')) {
            return LogUtil::registerError($this->__('Notice: Theme switching is currently disabled.'));
        }

        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_COMMENT)) {
            return LogUtil::registerPermissionError();
        }

        // get some use information about our environment
        $currenttheme = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));

        // get all themes in our environment
        $themes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);

        $previewthemes = array();
        $currentthemepic = null;
        foreach ($themes as $themeinfo) {
            $themename = $themeinfo['name'];
            if (file_exists($themepic = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/images/preview_medium.png')) {
                $themeinfo['previewImage'] = $themepic;
                $themeinfo['largeImage'] = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/images/preview_large.png';
            }
            else {
                $themeinfo['previewImage'] = 'system/Theme/images/preview_medium.png';
                $themeinfo['largeImage'] = 'system/Theme/images/preview_large.png';
            }
            $previewthemes[$themename] = $themeinfo;
            if ($themename == $currenttheme['name']) {
                $currentthemepic = $themepic;
            }
        }

        $this->view->setCaching(false);

        $this->view->assign('currentthemepic', $currentthemepic)
                   ->assign('currenttheme', $currenttheme)
                   ->assign('themes', $previewthemes)
                   ->assign('defaulttheme', ThemeUtil::getInfo(ThemeUtil::getIDFromName(System::getVar('Default_Theme'))));

        // Return the output that has been generated by this function
        return $this->view->fetch('theme_user_main.tpl');
    }

    /**
     * reset the current users theme to the site default
     */
    public function resettodefault()
    {
        ModUtil::apiFunc('Theme', 'user', 'resettodefault');
        LogUtil::registerStatus($this->__('Done! Theme has been reset to the default site theme.'));
        $this->redirect(ModUtil::url('Theme', 'user', 'main'));
    }
}