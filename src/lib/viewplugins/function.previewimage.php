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
 * Zikula_View function to display a preview image from a theme
 *
 * Available parameters:
 *  - name       name of the theme to display the preview image for
 *  - name       if set, the id assigned to the image
 *  - size         if set, the size of the image to use from small, medium, large (optional: default 'medium')
 *  - assign     if set, the title will be assigned to this variable
 *
 * Example
 * {previewimage name=andreas08 size=large}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.title.php::smarty_function_previewimage()
 *
 * @return string The markup to display the theme image.
 */
function smarty_function_previewimage($params, Zikula_View $view)
{
    if (!isset($params['name'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('previewimage', 'name')));

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
        $filesrc = "system/Theme/images/preview_{$params['size']}.png";
        if (!file_exists($filesrc)) {
            $filesrc = "system/Theme/pnimages/preview_{$params['size']}.png";
        }
    }

    $markup = "<img{$idstring} src=\"{$filesrc}\" alt=\"\" />";

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $markup);
    } else {
        return $markup;
    }
}
