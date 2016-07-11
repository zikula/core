<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View function to get current URI/URL to change language, handling in proper way short URLs
 *
 * This function obtains the current request URI and returns URI/URL with parameter to change language.
 * The results of this function are already sanitized to display, so it should not be passed to the safetext modifier.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - lang:     Language code to change to
 *   - fqurl:    Fully Qualified URL. True to get full URL, otherwise return URI
 *
 * Example
 *   {langchange lang='de'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The changed current URI.
 */
function smarty_function_langchange($params, Zikula_View $view)
{
    $assign = null;
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }
    $lang = null;
    if (isset($params['lang'])) {
        $lang = $params['lang'];
    }
    $fqurl = false;
    if (isset($params['fqurl'])) {
        $fqurl = $params['fqurl'];
        unset($params['fqurl']);
    }

    // Handling short URL's similar to Language selector block
    $shorturls = System::getVar('shorturls', false);
    if (isset($lang) && $shorturls) {
        $module = FormUtil::getPassedValue('module', null, 'GET', FILTER_SANITIZE_STRING);
        $type = FormUtil::getPassedValue('type', null, 'GET', FILTER_SANITIZE_STRING);
        $func = FormUtil::getPassedValue('func', null, 'GET', FILTER_SANITIZE_STRING);
        if (isset($module) && isset($type) && isset($func)) {
            // build URL based on module URL
            $result = ModUtil::url($module, $type, $func, $_GET, null, null, $fqurl, false, $lang);
        } else {
            // to homepage with language set in terms of short url's
            if ($fqurl) {
                $result = System::getVar('entrypoint', 'index.php') . "?lang=" . $lang;
            } else {
                $result = $lang;
            }
        }
    } else {
        if ($fqurl) {
            $result = htmlspecialchars(System::getCurrentUrl($params));
        } else {
            $result = htmlspecialchars(System::getCurrentUri($params));
        }
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
