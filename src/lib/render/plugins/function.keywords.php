<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to get the meta keywords
 *
 * This function will take the contents of the page and transfer it
 * into a keyword list. If stopwords are defined, they are filtered out.
 * The keywords are sorted by count.
 * As a default, the whole page contents are taken as a base for keyword
 * generation. If set, the contents of "contents" are taken.
 * Beware that the function always returns the site keywords if "generate
 * meta keywords" is turned off.
 * PLEASE NOTE: This function adds additional overhead when dynamic keyword
 * generation is turned on. You should use Xanthia page caching in this case.
 *
 * available parameters:
 *  - contents    if set, this wil be taken as a base for the keywords
 *  - dynamic     if set, the keywords will be created from the content / mainconent
 *                oterwise we use the page vars. The rules are:
 *                1) If dynamic keywords disabled in admin settings then use static keywords
 *                2) if parameter "dynamic" not set or empty then always use main content (default),
 *                3) if parameter "dynamic" set and not empty then use page vars if any set - otherwise use content.
 *  - assign      if set, the keywords will be assigned to this variable
 *
 * Example
 * <meta name="KEYWORDS" content="<!--[keywords]-->">
 *
 * @param    array    $params     All attributes passed to this function from the template
 * @param    object   $smarty     Reference to the Smarty object
 * @return   string   the keywords
 */
function smarty_function_keywords($params, &$smarty)
{
    $keywordsarray = PageUtil::getVar('keywords');
    if (!empty($keywordsarray)) {
        $ak = array_keys($keywordsarray);
        foreach ($ak as $v) {
            $keywordsarray[$v] = trim($keywordsarray[$v]);
            if (empty($keywordsarray[$v])) {
                unset($keywordsarray[$v]);
            }
        }
    }

    if (!empty($keywordsarray)) {
        $keywords = implode(',', $keywordsarray);
    } else {
        $keywords = pnConfigGetVar('metakeywords');
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $keywords);
    } else {
        return $keywords;
    }
}

if (!function_exists('html_entity_decode')) {
    /**
     * html_entity_decode()
     *
     * Convert all HTML entities to their applicable characters
     * This function is a fallback if html_entity_decode isn't defined
     * in the PHP version used (i.e. PHP < 4.3.0).
     * Please note that this function doesn't support all parameters
     * of the original html_entity_decode function.
     *
     * @param  string $string the this function converts all HTML entities to their applicable characters from string.
     * @return the converted string
     * @link http://php.net/html_entity_decode The documentation of html_entity_decode
     **/
    function html_entity_decode($string)
    {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        return (strtr($string, $trans_tbl));
    }
}


