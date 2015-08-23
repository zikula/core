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

namespace Zikula\ThemeModule\Controller;

use ModUtil;
use System;
use SecurityUtil;
use UserUtil;
use ThemeUtil;
use DataUtil;
use Zikula_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;

/**
 * User controllers for the theme module
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * 
     * display theme changing user interface
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws \RuntimeException Thrown if theme switching is currently disabled
     * @throws AccessDeniedException Thrown if the user doesn't have comment permissions over the theme module
     */
    public function indexAction(Request $request)
    {
        // check if theme switching is allowed
        if (!System::getVar('theme_change')) {
            $request->getSession()->getFlashBag()->add('warning', $this->__('Notice: Theme switching is currently disabled.'));
            return new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
        }

        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }

        // get our input
        $startnum = $request->query->get('startnum', isset($args['startnum']) ? $args['startnum'] : 1);

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
            } else {
                $themeinfo['previewImage'] = 'system/ThemeModule/Resources/public/images/preview_medium.png';
                $themeinfo['largeImage'] = 'system/ThemeModule/Resources/public/images/preview_large.png';
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

        return new Response($this->view->fetch('User/main.tpl'));
    }

    /**
     * @Route("/reset")
     * 
     * reset the current users theme to the site default
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function resettodefaultAction(Request $request)
    {
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'resettodefault');
        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Theme has been reset to the default site theme.'));

        return new RedirectResponse($this->get('router')->generate('zikulathememodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
    }
}
