<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use SecurityUtil;
use System;
use ZLanguage;
use BlockUtil;
use Zikula_View;
use FormUtil;
use ModUtil;
use Zikula_View_Theme;

/**
 * Block to display a language selection interface
 */
class LangBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Languageblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        // Requirement
        // $requirement_message must contain the error message or be empty
        $requirement_message = '';
        $multilanguageEnable = System::getVar('multilingual');
        if (!$multilanguageEnable) {
            $requirement_message .= $this->__('Notice: This language block will not be display until you enable the multilanguage, you can enable/disable this into into the settings of Zikula.');
        }

        return [
            'module'          => 'ZikulaBlocksModule',
            'text_type'       => $this->__('Language'),
            'text_type_long'  => $this->__('Language selector block'),
            'allow_multiple'  => false,
            'form_content'    => false,
            'form_refresh'    => false,
            'show_preview'    => true,
            'admin_tableless' => true,
            'requirement'     => $requirement_message
        ];
    }

    /**
     * Display the block
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string the rendered block
     */
    public function display($blockinfo)
    {
        // security check
        if (!SecurityUtil::checkPermission('Languageblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        // if the site's not an ML site don't display the block
        if (!System::getVar('multilingual')) {
            return;
        }

        $currentlanguage = ZLanguage::getLanguageCode();
        $languages = ZLanguage::getInstalledLanguages();

        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        $vars['bid'] = $blockinfo['bid'];
        // Defaults
        if (empty($vars['format'])) {
            $vars['format'] = 2;
        }

        if (empty($vars['fulltranslation'])) {
            $vars['fulltranslation'] = 1;
        }

        if ($vars['fulltranslation'] == 2) {
            foreach ($languages as $code) {
                // bind all languages, we'll need them later.
                ZLanguage::setLocale($code);
                ZLanguage::bindCoreDomain();
            }
            ZLanguage::setLocale($currentlanguage);
        }

        if (!isset($vars['languages']) || empty($vars['languages']) || !is_array($vars['languages'])) {
            $vars['languages'] = $this->getAvailableLanguages($vars['fulltranslation']);
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the block vars
        $this->view->assign($vars);

        $this->view->assign('currentlanguage', $currentlanguage);

        // set a block title
        if (empty($blockinfo['title'])) {
            $blockinfo['title'] = $this->__('Choose a language');
        }

        // prepare vars for ModUtil::url
        $module = FormUtil::getPassedValue('module', null, 'GET', FILTER_SANITIZE_STRING);
        $type = FormUtil::getPassedValue('type', null, 'GET', FILTER_SANITIZE_STRING);
        $func = FormUtil::getPassedValue('func', null, 'GET', FILTER_SANITIZE_STRING);
        $get = $_GET;
        if (isset($get['module'])) {
            unset($get['module']);
        }
        if (isset($get['type'])) {
            unset($get['type']);
        }
        if (isset($get['func'])) {
            unset($get['func']);
        }
        if (isset($get['lang'])) {
            unset($get['lang']);
        }

        if (System::isLegacyMode()) {
            if (!isset($type)) {
                $type = 'user';
            }
            if (!isset($func)) {
                $func = 'index';
            }
        }

        // make homepage calculations
        $shorturls = System::getVar('shorturls', false);

        if ($shorturls) {
            $homepage = System::getBaseUrl().System::getVar('entrypoint', 'index.php');
            $forcefqdn = true;
        } else {
            $homepage = System::getVar('entrypoint', 'index.php');
            $forcefqdn = false;
        }

        // build URLS

        $urls = [];
        foreach ($languages as $code) {
            if (isset($module) && isset($type) && isset($func)) {
                $thisurl = ModUtil::url($module, $type, $func, $get, null, null, $forcefqdn, !$shorturls, $code);
            } else {
                $thisurl = ($shorturls ? $code : "$homepage?lang=$code");
            }

            $codeFS = ZLanguage::transformFS($code);

            $flag = '';
            if ($vars['format']) {
                $flag = "images/flags/flag-$codeFS.png";
                if (!file_exists($flag)) {
                    $flag = '';
                }
                $flag = (($flag && $shorturls) ? System::getBaseUrl() . $flag : $flag);
            }

            if ($vars['fulltranslation'] == 2) {
                ZLanguage::setLocale($code);
            }

            $urls[] = [
                'code' => $code,
                'name' => ZLanguage::getLanguageName($code),
                'url' => $thisurl,
                'flag' => $flag
            ];

            if ($vars['fulltranslation'] == 2) {
                ZLanguage::setLocale($currentlanguage);
            }
        }
        usort($urls, [$this, '_blocks_thelangblock_sort']);

        $this->view->assign('urls', $urls);

        // get the block content from the template then end the templating
        $blockinfo['content'] = $this->view->fetch('Block/thelang.tpl');

        // return the block to the theme
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string the bock form
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        if (empty($vars['format'])) {
            $vars['format'] = 2;
        }

        if (empty($vars['fulltranslation'])) {
            $vars['fulltranslation'] = 1;
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the approriate values
        $this->view->assign($vars);

        // clear the block cache
        $this->view->clear_cache('Block/thelang.tpl');

        // Return the output that has been generated by this function
        return $this->view->fetch('Block/thelang_modify.tpl');
    }

    /**
     * update block settings
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return array $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Read inputs
        $vars['format'] = $this->request->request->get('format');

        // Read inputs
        $vars['fulltranslation'] = $this->request->request->get('fulltranslation');

        // Scan for languages and save cached version
        $vars['languages'] = $this->getAvailableLanguages(0);

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('Block/thelang.tpl');

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }

    /**
     * Get a list of available languages
     *
     * @param int $translate flag to localise the language name
     *
     * @return array list of languages
     */
    public function getAvailableLanguages($translate)
    {
        $savedLanguage = ZLanguage::getLanguageCode();
        $langlist = ZLanguage::getInstalledLanguages();

        $list = [];
        foreach ($langlist as $code) {
            $img = file_exists("images/flags/flag-$code.png");

            if ($translate == 2) {
                // configuration requires to translate each item in the list into the language of the country being shown
                ZLanguage::setLocale($code);
                $langname = ZLanguage::getLanguageName($code);
                ZLanguage::setLocale($savedLanguage);
            } else {
                $langname = ZLanguage::getLanguageName($code);
            }
            $list[] = [
                'code' => $code,
                'name' => $langname,
                'flag' => $img ? "images/flags/flag-$code.png" : ''
            ];
        }

        usort($list, [$this, '_blocks_thelangblock_sort']);

        return $list;
    }

    /**
     * Callback function to assist in sorting languages
     *
     * @param string $a the first language
     * @param string $b the second language
     *
     * @see LangBlock::getAvailableLanguages
     *
     * @return int <0 if $a < $b, >0 otherwise
     */
    public function _blocks_thelangblock_sort($a, $b)
    {
        return strcmp($a['name'], $b['name']);
    }
}
