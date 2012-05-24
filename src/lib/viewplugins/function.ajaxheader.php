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
 * Inserts the common ajax javascript files in page header.
 *
 * Insert the common ajax javascript files (prototype, scriptaculous) in the
 * page header using page vars.  <i>All other javascript files have to be added
 * manually on-demand using the {@link smarty_function_pageaddvar() pageaddvar} plugin.</i>
 *
 * Available attributes:
 *  - modname           (string)    the module name in which to look for the base javascript file for the module; defaults to top level module when used in a block template.
 *  - filename          (string)    (optional) filename to load (default ajax.js)
 *  - noscriptaculous   (mixed)     (optional) does not include scriptaculous.js if set
 *  - validation        (mixed)     (optional) includes validation.js if set
 *  - lightbox          (mixed)     (optional) includes lightbox.js if set (loads scriptaculous effects if noscriptaculous is set)
 *  - imageviewer       (mixed)     (optional) includes Zikula.ImageViewer.js if set (loads scriptaculous effects and dragdrop if noscriptaculous is set)
 *  - assign            (string)    (optional) the name of the template variable to which the script tag string is assigned, <i>instead of</i>
 *                                             adding them to the page variables through PageUtil::addVar
 *
 *
 * Examples:
 *
 * <samp>{ajaxheader modname='Example' filename='example.js'}</samp>
 *
 * <samp>{ajaxheader modname='Example' noscriptaculous=1}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_ajaxheader($params, Zikula_View $view)
{
    // use supplied modname or top level module
    $modname = (isset($params['modname'])) ? $params['modname'] : ModUtil::getName();
    // define the default filename
    $filename = (isset($params['filename'])) ? $params['filename'] : 'Zikula.js';
    $validation = (isset($params['validation'])) ? true : false;
    $lightbox = (isset($params['lightbox'])) ? true : false;
    $ui = (isset($params['ui'])) ? true : false;
    $imageviewer = (isset($params['imageviewer'])) ? true : false;

    // create an empty return
    $return = '';

    // we always need those
    $scripts = array('prototype', 'zikula');

    if ($validation) {
        $scripts[] = 'validation';
    }
    if ($ui) {
        $scripts[] = 'livepipe';
        $scripts[] = 'zikula.ui';
    }

    if ($lightbox) {
        // check if lightbox is present - if not, load ImageViewer instead
        if (is_readable('javascript/ajax/lightbox.js')) {
            $scripts[] = 'javascript/ajax/lightbox.js';
            if (isset($params['assign'])) {
                $return = '<link rel="stylesheet" href="javascript/ajax/lightbox/lightbox.css" type="text/css" media="screen" />';
            } else {
                PageUtil::addVar('stylesheet', 'javascript/ajax/lightbox/lightbox.css');
            }
        } else {
            $imageviewer = true;
        }
    }
    if ($imageviewer) {
        $scripts[] = 'zikula.imageviewer';
        if (isset($params['assign'])) {
            $return = '<link rel="stylesheet" href="javascript/helpers/ImageViewer/ImageViewer.css" type="text/css" media="screen" />';
        }
    }

    $modinfo = ModUtil::getInfoFromName($modname);
    if ($modinfo !== false) {
        $osdirectory = DataUtil::formatForOS($modinfo['directory']);
        $osfilename = DataUtil::formatForOS($filename);

        $base = $modinfo['type'] == ModUtil::TYPE_SYSTEM ? 'system' : 'modules';
        if (file_exists($file = "$base/$osdirectory/javascript/$osfilename") || file_exists($file = "$base/$osdirectory/pnjavascript/$osfilename")) {
            $scripts[] = DataUtil::formatForDisplay($file);
        }
    }

    if (isset($params['assign'])) {
        // create script tags now
        $scripts = JCSSUtil::prepareJavascripts($scripts);
        foreach ($scripts as $script) {
            $return .= '<script type="text/javascript" src="' . $script . '"></script>' . "\n";
        }
        $view->assign($params['assign'], $return);
    } else {
        PageUtil::addVar('javascript', $scripts);
    }

    return;
}
