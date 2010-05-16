<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display a preview image from a theme
 *
 * available parameters:
 *  - name       name of the theme to display the preview image for
 *  - name       if set, the id assigned to the image
 *  - size         if set, the size of the image to use from small, medium, large (optional: default 'medium')
 *  - assign     if set, the title will be assigned to this variable
 *
 * Example
 * <!--[previewimage name=andreas08 size=large]-->
 *
 * @see           function.title.php::smarty_function_previewimage()
 * @param      array       $params      All attributes passed to this function from the template
 * @param      object      &$smarty     Reference to the Smarty object
 * @return      string      the markup to display the theme image
 */
function smarty_function_previewimage($params, &$smarty)
{
    if (!isset($params['name'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('previewimage', 'name')));
        return false;
    }

    if (!isset($params['size']) || !in_array($params['size'], array('large', 'medium', 'small'))) {
        $params['size'] = 'medium';
    }

    $idstring = '';
    if (isset($params['id'])) {
        $idstring = " id=\"{$params['id']}\"";
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($params['name']));

    if (!file_exists($filesrc = "themes/{$themeinfo['directory']}/images/preview_{$params['size']}.png")) {
        $filesrc = "system/Theme/pnimages/preview_{$params['size']}.png";
    }

    $markup = "<img{$idstring} src=\"{$filesrc}\" alt=\"\" />";

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $markup);
    } else {
        return $markup;
    }
}
