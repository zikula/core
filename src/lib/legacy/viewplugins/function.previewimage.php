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
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['previewimage', 'name']));

        return false;
    }

    if (!isset($params['size']) || !in_array($params['size'], ['large', 'medium', 'small'])) {
        $params['size'] = 'medium';
    }

    $idstring = '';
    if (isset($params['id'])) {
        $idstring = " id=\"{$params['id']}\"";
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($params['name']));
    $theme = ThemeUtil::getTheme($themeinfo['name']);
    $themePath = null === $theme ? "themes/{$themeinfo['directory']}/images" : $theme->getRelativePath().'/Resources/public/images';
    if (file_exists("$themePath/preview_{$params['size']}.png")) {
        $filesrc = "$themePath/preview_{$params['size']}.png";
    } else {
        $filesrc = "system/ThemeModule/Resources/public/images/preview_{$params['size']}.png";
    }

    $markup = "<img{$idstring} src=\"{$filesrc}\" alt=\"\" />";

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $markup);
    } else {
        return $markup;
    }
}
