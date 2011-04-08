<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View function to get the meta keywords
 *
 * This function will take the contents of the page and transfer it
 * into a keyword list. If stopwords are defined, they are filtered out.
 * The keywords are sorted by count.
 * As a default, the whole page contents are taken as a base for keyword
 * generation. If set, the contents of "contents" are taken.
 * Beware that the function always returns the site keywords if "generate
 * meta keywords" is turned off.
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
 * <meta name="KEYWORDS" content="{keywords}">
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The keywords.
 */
function smarty_function_keywords($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated.', array('keywords')), E_USER_DEPRECATED);

    $metatags = $view->getServiceManager()->getArgument('zikula_view.metatags');

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $metatags['keywords']);
    } else {
        return $metatags['keywords'];
    }
}
