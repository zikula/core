<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Block;

use BlockUtil;
use DataUtil;
use ModUtil;
use SecurityUtil;
use System;
use UserUtil;
use ThemeUtil;
use Zikula_View;
use Zikula_View_Theme;

/**
 * @deprecated at Core-2.0 - This block will not be converted to Twig nor be available in Core-2.0
 * Block to display a theme switching interface
 */
class ThemeswitcherBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Themeswitcherblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        $switchThemeEnable = System::getVar('theme_change');

        if (!$switchThemeEnable) {
            $requirement_message = $this->__f('Notice: This theme switcher block will not be displayed until you allow users to change themes. You can enable/disable this from the <a href="%s">settings</a> of the Theme module.', DataUtil::formatForDisplayHTML($this->get('router')->generate('zikulathememodule_admin_modifyconfig')));
        } else {
            $requirement_message = '';
        }

        return [
            'module'       => 'ZikulaThemeModule',
            'text_type'         => $this->__('Theme switcher'),
            'text_type_long'    => $this->__('Theme switcher'),
            'allow_multiple'    => true,
            'form_content'      => false,
            'form_refresh'      => false,
            'show_preview'      => true,
            'admin_tableless'   => true,
            'requirement'       => $requirement_message
        ];
    }

    /**
     * render the theme switching block
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string rendered block
     */
    public function display($blockinfo)
    {
        // check if the module is available
        if (!ModUtil::available('ZikulaThemeModule')) {
            return;
        }

        // check if theme switching is allowed
        if (!System::getVar('theme_change')) {
            return;
        }

        // security check
        if (!SecurityUtil::checkPermission("Themeswitcherblock::", "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        if (empty($vars['format'])) {
            $vars['format'] = 1;
        }

        // get some use information about our environment
        $currenttheme = UserUtil::getTheme();

        // get all themes in our environment
        $themes = ThemeUtil::getAllThemes();

        // get some use information about our environment
        $currenttheme = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));

        // get all themes in our environment
        $themes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);

        $previewthemes = [];
        $currentthemepic = null;
        foreach ($themes as $themeinfo) {
            $themename = $themeinfo['name'];
            if (file_exists($themepic = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/images/preview_small.png')) {
                $themeinfo['previewImage'] = $themepic;
            } elseif (file_exists($themepic = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/Resources/public/images/preview_small.png')) {
                $themeinfo['previewImage'] = $themepic;
            } else {
                $themeinfo['previewImage'] = 'system/ThemeModule/Resources/public/images/preview_small.png';
            }
            $previewthemes[$themename] = $themeinfo;
            if ($themename == $currenttheme['name']) {
                $currentthemepic = $themeinfo['previewImage'];
            }
        }

        $this->view->assign($vars)
                   ->assign('currentthemepic', $currentthemepic)
                   ->assign('currenttheme', $currenttheme)
                   ->assign('themes', $previewthemes);

        $blockinfo['content'] = $this->view->fetch('Block/themeswitcher.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * render the theme switching block modifcation options
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string rendered block modify form components
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        // format: 1 = drop down with preview, 2 = simple list
        if (empty($vars['format'])) {
            $vars['format'] = 1;
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the approriate values
        $this->view->assign($vars);

        // Return the output that has been generated by this function
        return $this->view->fetch('Block/themeswitcher_modify.tpl');
    }

    /**
     * update the theme switching block
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return array modified block info array
     */
    public function update($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // alter the corresponding variable
        $vars['format'] = $this->request->request->get('format', 1);

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('Block/themeswitcher.tpl');

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }
}
