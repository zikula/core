<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Controller;

use DataUtil;
use ModUtil;
use System;
use ThemeUtil;
use UserUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * User controllers for the theme module
 * @deprecated at Core-2.0 This controller and feature of 'theme switching' will not be converted and will not be
 *   available in Core-2.0
 */
class UserController extends AbstractController
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
        $variableApi = $this->get('zikula_extensions_module.api.variable');
        // check if theme switching is allowed
        if (!$variableApi->get(VariableApi::CONFIG, 'theme_change')) {
            $this->addFlash('warning', $this->__('Notice: Theme switching is currently disabled.'));

            return new RedirectResponse(System::normalizeUrl(System::getHomepageUrl()));
        }

        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }

        // get our input
        $startnum = $request->query->getDigits('startnum', isset($args['startnum']) ? $args['startnum'] : 1);

        // we need this value multiple times, so we keep it
        $itemsPerPage = $variableApi->get('ZikulaThemeModule', 'itemsperpage');

        // get some use information about our environment
        $currenttheme = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));

        // get all themes in our environment
        $allthemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);

        $previewThemes = [];
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
                $previewThemes[$themename] = $themeinfo;
            }
        }

        $previewThemes = array_slice($previewThemes, $startnum - 1, $itemsPerPage);

        $this->view->assign('currentthemepic', $currentthemepic)
                   ->assign('currenttheme', $currenttheme)
                   ->assign('themes', $previewThemes)
                   ->assign('defaulttheme', ThemeUtil::getInfo(ThemeUtil::getIDFromName($variableApi->get(VariableApi::CONFIG, 'Default_Theme'))));

        // assign the values for the pager plugin
        $this->view->assign('pager', [
            'numitems' => count($allthemes),
            'itemsperpage' => $itemsPerPage
        ]);

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
        $this->addFlash('status', $this->__('Done! Theme has been reset to the default site theme.'));

        return $this->redirectToRoute('zikulathememodule_user_index');
    }
}
