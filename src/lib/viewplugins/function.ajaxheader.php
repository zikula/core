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
 *  - filename          (string)    (optional) filename to load (default none)
 *  - validation        (mixed)     (optional) includes validation.js if set
 *  - lightbox          (mixed)     (optional) includes core lightbox script
 *  - ui                (bool)      (optional) includes core ui helpers (zikula.ui and jquery ui)
 *
 * Examples:
 *
 * <samp>{ajaxheader modname='Example' filename='example.js'}</samp>
 *
 * <samp>{ajaxheader modname='Example'}</samp>
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
    $filename = (isset($params['filename'])) ? $params['filename'] : false;
    $validation = (isset($params['validation'])) ? true : false;
    $lightbox = (isset($params['lightbox'])) ? true : false;
    $ui = (isset($params['ui'])) ? true : false;
    $imageviewer = (isset($params['imageviewer'])) ? true : false;

    // we always need those
    $scripts = array('zikula');

    if ($validation) {
        $scripts[] = 'validation';
    }
    if ($ui) {
        $scripts[] = 'zikula.ui';
    }
    if ($lightbox || $imageviewer) {
        $scripts[] = 'colorbox';
    }

    $modinfo = ModUtil::getInfoFromName($modname);
    if ($modinfo !== false && $filename) {
        $osdirectory = DataUtil::formatForOS($modinfo['directory']);
        $osfilename = DataUtil::formatForOS($filename);

        $base = $modinfo['type'] == ModUtil::TYPE_SYSTEM ? 'system' : 'modules';
        if (file_exists($file = "$base/$osdirectory/Resources/public/js/$osfilename") || file_exists($file = "$base/$osdirectory/javascript/$osfilename")) {
            $scripts[] = DataUtil::formatForDisplay($file);
        }
    }

    PageUtil::addVar('javascript', $scripts);


    return;
}
