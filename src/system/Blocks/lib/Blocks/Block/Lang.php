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

class Blocks_Block_Lang extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Languageblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return       array       The block information
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

        return array('module'          => 'Blocks',
                     'text_type'       => $this->__('Language'),
                     'text_type_long'  => $this->__('Language selector block'),
                     'allow_multiple'  => false,
                     'form_content'    => false,
                     'form_refresh'    => false,
                     'show_preview'    => true,
                     'admin_tableless' => true,
                     'requirement'     => $requirement_message);
    }

    /**
     * Display the block
     *
     * @param        row           blockinfo array
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
                $func = 'main';
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

        $urls = array();
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

            $urls[] = array('code' => $code, 'name' => ZLanguage::getLanguageName($code), 'url' => $thisurl, 'flag' => $flag);

            if ($vars['fulltranslation'] == 2) {
                ZLanguage::setLocale($currentlanguage);
            }
        }
        usort($urls, '_blocks_thelangblock_sort');

        $this->view->assign('urls', $urls);

        // get the block content from the template then end the templating
        $blockinfo['content'] = $this->view->fetch('blocks_block_thelang.tpl');

        // return the block to the theme
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
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
        $this->view->clear_cache('blocks_block_thelang.tpl');

        // Return the output that has been generated by this function
        return $this->view->fetch('blocks_block_thelang_modify.tpl');
    }

    /**
     * update block settings
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Read inputs
        $vars['format'] = FormUtil::getPassedValue('format');

        // Read inputs
        $vars['fulltranslation'] = FormUtil::getPassedValue('fulltranslation');

        // Scan for languages and save cached version
        $vars['languages'] = $this->getAvailableLanguages();

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('blocks_block_thelang.tpl');

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }

    public function getAvailableLanguages($translate)
    {
        $savedLanguage = ZLanguage::getLanguageCode();
        $langlist = ZLanguage::getInstalledLanguages();

        $list = array();
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
            $list[] = array('code' => $code,
                            'name' => $langname,
                            'flag' => $img ? "images/flags/flag-$code.png" : '');
        }

        usort($list, '_blocks_thelangblock_sort');

        return $list;
    }
}

function _blocks_thelangblock_sort($a, $b)
{
    return strcmp($a['name'], $b['name']);
}
