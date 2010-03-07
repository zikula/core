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
 * Smarty function to insert the common ajax javascript files (prototype, scriptaculous) in the page header using page vars
 * All other jsvascript files have to added manually on demand using the PageUtil::addVar plugin
 *
 * Available parameters:
 *   - modname:          define the module name in which to look for the base javascript file for the module (ajax.js), default to top level module
 *                       when used in a block template, make sure this parameter is set correctly!
 *   - filename:         (optional) filename to load (default ajax.js)
 *   - noscriptaculous:  (optional) does not include scriptaculous.js if set
 *   - validation:       (optional) includes validation.js if set
 *   - fabtabulous:      (optional) includes fabtabulous.js if set
 *   - builder:          (optional) includes builder.js if set. Only effective if noscriptaculous is set
 *   - effects:          (optional) includes effects.js if set. Only effective if noscriptaculous is set
 *   - dragdrop:         (optional) includes dragdrop.js if set. Only effective if noscriptaculous is set
 *   - controls:         (optional) includes controls.js if set. Only effective if noscriptaculous is set
 *   - slider:           (optional) includes slider.js if set. Only effective if noscriptaculous is set
 *   - lightbox:         (optional) includes lightbox.js if set (loads scriptaculous effects if noscriptaculous is set)
 *   - imageviewer:      (optional) includes Zikula.ImageViewer.js if set (loads scriptaculous effects and dragdrop if noscriptaculous is set)
 *   - assign:           (optional) creates script tags and assign them if set
 *
 *
 * Examples:
 *   <!--[pnajaxheader modname=Example filename=example.js]-->
 *   <!--[pnajaxheader modname=Example noscriptaculous=1]-->
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       null
 */
function smarty_function_ajaxheader($params, &$smarty)
{
    // use supplied modname or top level module
    $modname       = (isset($params['modname']))         ? $params['modname']  : pnModGetName();
    // define the default filename
    $filename      = (isset($params['filename']))        ? $params['filename'] : 'ajax.js';
    $scriptaculous = (isset($params['noscriptaculous'])) ? false     : true;
    $validation    = (isset($params['validation']))      ? true      : false;
    $fabtabulous   = (isset($params['fabtabulous']))     ? true      : false;
    $lightbox      = (isset($params['lightbox']))        ? true      : false;
    $imageviewer   = (isset($params['imageviewer']))     ? true      : false;
    // script.aculo.us components
    $builder       = (isset($params['builder']))         ? true      : false;
    $effects       = (isset($params['effects']))         ? true      : false;
    $dragdrop      = (isset($params['dragdrop']))        ? true      : false;
    $controls      = (isset($params['controls']))        ? true      : false;
    $slider        = (isset($params['slider']))          ? true      : false;

    // create an empty return
    $return = '';

    $modinfo = pnModGetInfo(pnModGetIDFromName($modname));
    if ($modinfo == false) {
        $smarty->trigger_error('pnajaxheader: '.__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($modname)));
        return false;
    }

    // we always need those
    $scripts = array('javascript/ajax/prototype.js', 'javascript/ajax/pnajax.js');

    if ($scriptaculous == true) {
        $scripts[] = 'javascript/ajax/scriptaculous.js';
    }
    if ($validation) {
        $scripts[] = 'javascript/ajax/validation.js';
    }
    if ($fabtabulous) {
        $scripts[] = 'javascript/ajax/fabtabulous.js';
    }
    // script.aculo.us components
    if (!$scriptaculous && $builder) {
        $scripts[] = 'javascript/ajax/scriptaculous.js?load=builder';
    }
    if (!$scriptaculous && ($effects || $lightbox || $imageviewer)) {
        $scripts[] = 'javascript/ajax/scriptaculous.js?load=effects';
    }
    if (!$scriptaculous && ($dragdrop || $lightbox || $imageviewer)) {
        $scripts[] = 'javascript/ajax/scriptaculous.js?load=dragdrop';
    }
    if (!$scriptaculous && $controls) {
        $scripts[] = 'javascript/ajax/scriptaculous.js?load=controls';
    }
    if (!$scriptaculous && $slider) {
        $scripts[] = 'javascript/ajax/scriptaculous.js?load=slider';
    }
    if ($lightbox) {
        // check if lightbox is present - if not, load ImageViewer instead
        if(is_readable('javascript/ajax/lightbox.js')) {
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
        $scripts[] = 'javascript/helpers/Zikula.ImageViewer.js';
        if (isset($params['assign'])) {
            $return = '<link rel="stylesheet" href="javascript/helpers/ImageViewer/ImageViewer.css" type="text/css" media="screen" />';
        } else {
            PageUtil::addVar('stylesheet', 'javascript/helpers/ImageViewer/ImageViewer.css');
        }
    }

    $osdirectory = DataUtil::formatForOS($modinfo['directory']);
    $osfilename  = DataUtil::formatForOS($filename);

    if (($modinfo['type'] == 3 && file_exists($file = "system/$osdirectory/pnjavascript/$osfilename")) ||
       ($modinfo['type'] == 2 && file_exists($file = "modules/$osdirectory/pnjavascript/$osfilename"))) {
        $scripts[] = DataUtil::formatForDisplay($file);
    }

    if (isset($params['assign'])) {
        // create script tags now
        foreach($scripts as $script) {
            $return .= '<script type="text/javascript" src="' . $script . '"></script' . "\n";
        }
        $smarty->assign($params['assign'], $return);
    } else {
        PageUtil::addVar('javascript', $scripts);
    }

    return;
}
