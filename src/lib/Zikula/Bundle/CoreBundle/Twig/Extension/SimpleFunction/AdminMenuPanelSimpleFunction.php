<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction;

use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;

class AdminMenuPanelSimpleFunction
{
    private $twigExtension;

    /**
     * AdminMenuPanelSimpleFunction constructor.
     */
    public function __construct(CoreExtension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    /**
     * Inserts a hidden admin panel controlled by permissions.
     *
     * Inserts required javascript and css files for a hidden admin panel that is triggered by a rendered link.
     * Builds and renders an unordered list of admin-capable modules and their adminLinks using the
     * jQuery.mmenu library <@see http://mmenu.frebsite.nl>
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{( adminPanelMenu() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        if (!\SecurityUtil::checkPermission('ZikulaAdminModule::', "::", ACCESS_EDIT)) {
            return ''; // Since no permission, return empty
        }

        // add required scritps and stylesheets to page
        $this->twigExtension->getContainer()->get('zikula_core.common.theme.assets_js')->add($this->twigExtension->getAssetPath('@ZikulaAdminModule:js/jQuery.mmenu-5.5.1/dist/core/js/jquery.mmenu.min.all.js'));
        $this->twigExtension->getContainer()->get('zikula_core.common.theme.assets_css')->add($this->twigExtension->getAssetPath('@ZikulaAdminModule:js/jQuery.mmenu-5.5.1/dist/core/css/jquery.mmenu.all.css'));
        // add override for panel width created from .scss file
        $this->twigExtension->getContainer()->get('zikula_core.common.theme.assets_css')->add($this->twigExtension->getAssetPath('@ZikulaAdminModule:css/mmenu-hiddenpanel-customwidth.css'));

        $router = $this->twigExtension->getContainer()->get('router');
        $modules = \ModUtil::getModulesCapableOf('admin');
        $uri = $this->twigExtension->getContainer()->get('request')->getUri();
        $baseUrl = $this->twigExtension->getContainer()->get('request')->getBaseUrl();
        // sort modules by displayname
        $moduleNames = array();
        foreach ($modules as $key => $module) {
            $moduleNames[$key] = $module['displayname'];
        }
        array_multisort($moduleNames, SORT_ASC, $modules);

        // create unordered list of admin-capable module links
        $htmlContent = '<nav id="zikula-admin-hiddenpanel-menu">';
        $htmlContent .= '<div class="text-left">';
        $htmlContent .= '<h1><img src="' . $baseUrl . '/images/logo.gif" alt="Logo" style="height: 32px"> ' . __('Administration') . '</h1>';
        $htmlContent .= '<ul>';
        foreach ($modules as $module) {
            if (\SecurityUtil::checkPermission("module[name]::", '::', ACCESS_EDIT)) {
                // first-level list - list modules with general 'index' link
                $img = $baseUrl . '/' . \ModUtil::getModuleImagePath($module['name']);
                $url = isset($module['capabilities']['admin']['url'])
                    ? $module['capabilities']['admin']['url']
                    : $router->generate($module['capabilities']['admin']['route']);
                $moduleSelected = empty($moduleSelected) && strpos($uri, $module['url']) ? " class='Selected'" : "";
                $htmlContent .= "<li{$moduleSelected}><a href=\"" . \DataUtil::formatForDisplay($url) . "\"><img src=\"$img\" alt=\"\" style=\"height: 18px\" /> " . $module['displayname'] . "</a>";

                $links = $this->twigExtension->getContainer()->get('zikula.link_container_collector')->getLinks($module['name'], 'admin');
                if (empty($links)) {
                    $links = (array)\ModUtil::apiFunc($module['name'], 'admin', 'getLinks');
                }

                if ((count($links) > 0) && ($links[0] != false)) {
                    // create second-level list from module adminLinks
                    $htmlContent .= '<ul class="text-left">';
                    foreach ($links as $link) {
                        if (isset($link['icon'])) {
                            $img = '<i class="fa fa-' . $link['icon'] . '"></i>';
                        } elseif (isset($link['class'])) {
                            $img = '<span class="' . $link['class'] . '"></span>';
                        } else {
                            $img = '';
                        }
                        $linkSelected = empty($linkSelected) && strpos($uri, $link['url']) ? " class='Selected'" : "";
                        $htmlContent .= "<li{$linkSelected}><a href=\"" . \DataUtil::formatForDisplay($link['url']) . "\">$img " . $link['text'] . '</a>';
                        // create third-level list from adminLinks subLinks
                        if (isset($link['links']) && count($link['links']) > 0) {
                            $htmlContent .= '<ul class="text-left">';
                            foreach ($link['links'] as $sublink) {
                                $htmlContent .= '<li><a href="' . \DataUtil::formatForDisplay($sublink['url']) . '">' . $sublink['text'] . '</a></li>';
                            }
                            $htmlContent .= '</ul>';
                        }
                        $htmlContent .= '</li>';
                    }
                    $htmlContent .= '</ul>';
                }
                $htmlContent .= '</li>';
            }
        }
        $htmlContent .= '</ul>';
        $htmlContent .= '</div>';
        $htmlContent .= '</nav>';
        $htmlContent .= '
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    $("#zikula-admin-hiddenpanel-menu").mmenu({
                        extensions: ["hiddenpanel-customwidth"],
                        "header": {
                           "title": "' . __('Zikula Administration') . '",
                           "add": true,
                           "update": true
                        },
                        "searchfield": true
                    });
                });
            </script>';

        // the the html content before </body>
        $this->twigExtension->getContainer()->get('zikula_core.common.theme.assets_footer')->add($htmlContent);

        // display the control link
        return '<a href="#zikula-admin-hiddenpanel-menu"><i class="fa fa-bars"></i></a>';
    }
}
