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

namespace ThemeModule\Controller;

use System, SecurityUtil, LogUtil, ThemeUtil, ModUtil, UserUtil, DataUtil, Zikula_View;

class UserController extends \Zikula_AbstractController
{
    /**
     * display theme changing user interface
     */
    public function indexAction()
    {
        // check if theme switching is allowed
        if (!System::getVar('theme_change')) {
            LogUtil::registerError($this->__('Notice: Theme switching is currently disabled.'));
            return $this->redirect(ModUtil::url('Users', 'user', 'index'));
        }

        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_COMMENT)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        // get our input
        $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 1, 'GET');

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

        // get some use information about our environment
        $currenttheme = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));

        // get all themes in our environment
        $allthemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);

        $previewthemes = array();
        $currentthemepic = null;
        foreach ($allthemes as $key => $themeinfo) {
            $themename = $themeinfo['name'];
            if (file_exists($themepic = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/Resources/public/images/preview_medium.png')) {
                $themeinfo['previewImage'] = $themepic;
                $themeinfo['largeImage'] = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/Resources/public/images/preview_large.png';
            }
            else {
                $themeinfo['previewImage'] = 'system/Theme/Resources/public/images/preview_medium.png';
                $themeinfo['largeImage'] = 'system/Theme/Resources/public/images/preview_large.png';
            }
            if ($themename == $currenttheme['name']) {
                $currentthemepic = $themepic;
                unset($allthemes[$key]);
            } else {
                $previewthemes[$themename] = $themeinfo;
            }
        }

        $previewthemes = array_slice($previewthemes, $startnum-1, $itemsperpage);

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('currentthemepic', $currentthemepic)
                   ->assign('currenttheme', $currenttheme)
                   ->assign('themes', $previewthemes)
                   ->assign('defaulttheme', ThemeUtil::getInfo(ThemeUtil::getIDFromName(System::getVar('Default_Theme'))));

        // assign the values for the pager plugin
        $this->view->assign('pager', array('numitems' => sizeof($allthemes),
                                           'itemsperpage' => $itemsperpage));

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('theme_user_main.tpl'));
    }

    /**
     * reset the current users theme to the site default
     */
    public function resettodefaultAction()
    {
        ModUtil::apiFunc('ThemeModule', 'user', 'resettodefault');
        LogUtil::registerStatus($this->__('Done! Theme has been reset to the default site theme.'));
        return $this->redirect(ModUtil::url('Theme', 'user', 'index'));
    }
}