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
 * Zikula_View function to provide easy access to an image
 *
 * This function provides an easy way to include an image. The function will return the
 * full source path to the image. It will as well provite the width and height attributes
 * if none are set.
 *
 * Available parameters:
 *   - src            The file name of the image
 *   - modname        The well-known name of a module (default: the current module)
 *   - modplugin      The name of the plugin in the passed module
 *   - sysplugin      The name of the system plugin
 *   - width, height  If set, they will be passed. If none is set, they are obtained from the image
 *   - alt            If not set, an empty string is being assigned
 *   - title          If set it will be passed as a title attribute
 *   - assign         If set, the results are assigned to the corresponding variable instead of printed out
 *   - optional       If set then the plugin will not return an error if an image is not found
 *   - default        If set then a default image is used should the requested image not be found (Note: full path required)
 *   - set            If modname is 'core' then the set parameter is set to define the directory in /images/
 *   - nostoponerror  If set and error ocurs (image not found or src is no image), do not trigger_error, but return false
 *   - retval         If set indicated the field to return instead the array of values (src, width, etc.)
 *   - fqurl          If set the image path is absolute, if not relative
 *   - all remaining parameters are passed to the image tag
 *
 * Example: {img src='heading.png'}
 * Output:  <img src="modules/Example/images/en/heading.png" alt="" width="261" height="69" />
 *
 * Example: {img src='heading.png' width='100' border='1' __alt='foobar'}
 * Output:  <img src="modules/Example/images/en/heading.png" width="100" border="1" alt="foobar" />
 *
 * Example: {img src='xhtml11.png' modname='core' set='powered'}
 * Output:  <img src="themes/Theme/images/powered/xhtml11.png" alt="" width="88" height="31" />
 *
 * Example: {img src='iconX.png' modname='ModName' modplugin='Plug1' set='icons'}
 * Output:  <img src="modules/ModName/plugins/Plug1/images/icons/iconX.png" alt="" width="16" height="16" />
 *
 * Example: {img src='iconY.png' sysplugin='Plug2' set='icons/small'}
 * Output:  <img src="plugins/Plug2/images/icons/small/iconY.png" alt="" width="16" height="16" />
 *
 * If the parameter assign is set, the results are assigned as an array. The components of
 * this array are the same as the attributes of the img tag; additionally an entry 'imgtag' is
 * set to the complete image tag.
 *
 * Example:
 * {img src="heading.png" assign="myvar"}
 * {$myvar.src}
 * {$myvar.width}
 * {$myvar.imgtag}
 *
 * Output:
 * modules/Example/images/en/heading.gif
 * 261
 * <img src="modules/Example/images/en/heading.gif" alt="" width="261" height="69"  />
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string|void The img tag, null if $params['nostoponerror'] true and there is an error.
 */
function smarty_function_img($params, Zikula_View $view)
{
    $nostoponerror = isset($params['nostoponerror']) && $params['nostoponerror'] ? true : false;

    if (!isset($params['src']) || !$params['src']) {
        if (!$nostoponerror) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('img', 'src')));

            return;
        } else {
            return false;
        }
    }

    // process the image location
    $modname   = isset($params['modname']) ? $params['modname'] : $view->toplevelmodule;
    $modplugin = isset($params['modplugin']) ? $params['modplugin'] : null;
    $sysplugin = isset($params['sysplugin']) ? $params['sysplugin'] : null;

    // process the image set
    $set   = isset($params['set']) ? $params['set'] : null;
    $osset = DataUtil::formatForOS($set);

    // if the module name is 'core'
    if ($modname == 'core') {
        if (System::isLegacyMode() && (strpos($osset, 'icons/') !== false || strpos($osset, 'global/') !== false) && strpos($params['src'], '.gif')) {
            LogUtil::log(__f('Core image %s does not exist, please use the png format (called from %s).', array($params['src'], $view->getTemplatePath())), E_DEPRECATED);
            $params['src'] = str_replace('.gif', '.png', $params['src']);
        }
    }

    // always provide an alt attribute.
    // if none is set, assign an empty one.
    $params['alt'] = isset($params['alt']) ? $params['alt'] : '';

    // prevent overwriting surrounding titles (#477)
    if (isset($params['title']) && empty($params['title'])) {
        unset($params['title']);
    }

    // language
    $lang =  ZLanguage::transformFS(ZLanguage::getLanguageCode());

    if ($sysplugin) {
        $osplugdir    = DataUtil::formatForOS($sysplugin);
        $pluglangpath = "plugins/$osplugdir/images/$lang";
        $plugpath     = "plugins/$osplugdir/images";

        // form the array of paths
        $paths = array($pluglangpath, $plugpath);

    } else {
        // module directory
        $modinfo   = ModUtil::getInfoFromName($modname);
        $osmoddir  = DataUtil::formatForOS($modinfo['directory']);
        $moduleDir = ($modinfo['type'] == ModUtil::TYPE_SYSTEM ? 'system' : 'modules');

        if ($modplugin) {
            $osmodplugdir    = DataUtil::formatForOS($modplugin);
            $modpluglangpath = "$moduleDir/$osmoddir/plugins/$osmodplugdir/images/$lang";
            $modplugpath     = "$moduleDir/$osmoddir/plugins/$osmodplugdir/images";

            // form the array of paths
            $paths = array($modpluglangpath, $modplugpath);

        } else {
            // theme directory
            $ostheme       = DataUtil::formatForOS(UserUtil::getTheme());
            $osmodname     = DataUtil::formatForOS($modname);
            $themelangpath = "themes/$ostheme/templates/modules/$osmodname/images/$lang";
            $themepath     = "themes/$ostheme/templates/modules/$osmodname/images";
            $corethemepath = "themes/$ostheme/images";

            if ($modname == 'core') {
                $modpath        = "images";
            } else {
                $modlangpath    = "$moduleDir/$osmoddir/images/$lang";
                $modpath        = "$moduleDir/$osmoddir/images";
                $modlangpathOld = "$moduleDir/$osmoddir/pnimages/$lang";
                $modpathOld     = "$moduleDir/$osmoddir/pnimages";
            }

            // form the array of paths
            if ($modname == 'core') {
                $paths = array($themepath, $corethemepath, $modpath);
            } elseif (preg_match('/^admin.(png|gif|jpg)$/', $params['src'])) {
                // special processing for modules' admin icon
                $paths = array($modlangpath, $modpath, $modlangpathOld, $modpathOld);
            } else {
                $paths = array($themelangpath, $themepath, $corethemepath, $modlangpath, $modpath, $modlangpathOld, $modpathOld);
            }
        }
    }

    $ossrc = DataUtil::formatForOS($params['src']);

    // search for the image
    $imgsrc = '';
    foreach ($paths as $path) {
        $fullpath = $path . ($osset ? "/$osset/" : '/') . $ossrc;
        if (is_readable($fullpath)) {
            $imgsrc = $fullpath;
            break;
        }
    }

    if ($imgsrc == '' && isset($params['default'])) {
        $imgsrc = $params['default'];
    }

    // default for the optional flag
    $optional = isset($params['optional']) ? $params['optional'] : true;

    if ($imgsrc == '') {
        if ($optional) {
            if (!$nostoponerror) {
                $view->trigger_error(__f("%s: Image '%s' not found", array('img', DataUtil::formatForDisplay(($set ? "$set/" : '') . $params['src']))));

                return;
            } else {
                return false;
            }
        }

        return;
    }

    // If neither width nor height is set, get these parameters.
    // If one of them is set, we do NOT obtain the real dimensions.
    // This way it is easy to scale the image to a certain dimension.
    if (!isset($params['width']) && !isset($params['height'])) {
        if (!($_image_data = @getimagesize($imgsrc))) {
            if (!$nostoponerror) {
                $view->trigger_error(__f("%s: Image '%s' is not a valid image file", array('img', DataUtil::formatForDisplay(($set ? "$set/" : '') . $params['src']))));

                return;
            } else {
                return false;
            }
        }
        $params['width']  = $_image_data[0];
        $params['height'] = $_image_data[1];
    }

    $basepath = (isset($params['fqurl']) && $params['fqurl']) ? System::getBaseUrl() : System::getBaseUri();
    $params['src'] = $basepath . '/' . $imgsrc;

    $retval = isset($params['retval']) ? $params['retval'] : null;
    $assign = isset($params['assign']) ? $params['assign'] : null;

    unset($params['modname']);
    unset($params['retval']);
    unset($params['assign']);
    if (isset($params['altml'])) {
        // legacy
        unset($params['altml']);
    }
    if (isset($params['titleml'])) {
        // legacy
        unset($params['titleml']);
    }
    unset($params['optional']);
    unset($params['default']);
    unset($params['set']);
    unset($params['nostoponerror']);
    unset($params['fqurl']);

    $imgtag = '<img ';
    foreach ($params as $key => $value) {
        $imgtag .= $key . '="' .$value  . '" ';
    }
    $imgtag .= '/>';

    if (!empty($retval) && isset($params[$retval])) {
        return $params[$retval];

    } elseif (!empty($assign)) {
        $params['imgtag'] = $imgtag;
        $view->assign($assign, $params);

    } else {
        return $imgtag;
    }
}
