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
 * Smarty function to provide easy access to an image
 *
 * This function provides an easy way to include an image. The function will return the
 * full source path to the image. It will as well provite the width and height attributes
 * if none are set.
 *
 * Available parameters:
 *   - type:          The type of image to render (example: save)
 *   - size:           The size of the image (extrasmall - small - large - default:extrasmall)
 *   - width, height: If set, they will be passed. If none is set, they are obtained from the 'size' parameter
 *   - alt:           If not set, an empty string is being assigned
 *   - title:         If not set, an empty string is being assigned
 *   - assign:        If set, the results are assigned to the corresponding variable instead of printed out
 *   - optional       If set then the plugin will not return an error if an image is not found
 *   - default        If set then a default image is used should the requested image not be found (Note: full path required)
 *   - all remaining parameters are passed to the image tag
 *
 * Example: <!--[pnicon type="save" size="extrasmall" altml="_SAVE"]-->
 * Output:  <img src="images/icons/extrasmall/save.png" alt="Save" />
 *
 * Example: <!--[pnicon type="save" width="100" border="1" alt="foobar" ]-->
 * Output:  <img src="images/icons/extrasmall/save.png" width="100" border="1" alt="foobar"  />
 *
 * If the parameter assign is set, the results are assigned as an array. The components of
 * this array are the same as the attributes of the img tag; additionally an entry 'imgtag' is
 * set to the complete image tag.
 *
 * Example:
 * <!--[pnicon src="heading.gif" assign="myvar"]-->
 * <!--[$myvar.src]-->
 * <!--[$myvar.width]-->
 * <!--[$myvar.imgtag]-->
 *
 * Output:
 * modules/Example/pnimages/eng/heading.gif
 * 261
 * <img src="modules/Example/pnimages/eng/heading.gif" alt="" width="261" height="69"  />
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      The img tag
 */
function smarty_function_icon($params, &$smarty)
{
    if (!isset($params['type'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_icon', 'type')));
        return false;
    }

    // default for the optional flag
    $optional = isset($params['optional']) ? $params['optional'] : true;

    // always provide an alt attribute.
    // if none is set, assign an empty one.
    $params['alt']    = isset($params['alt'])     ? $params['alt']     : '';
    $params['title']  = isset($params['title'])   ? $params['title']   : $params['alt'];
    $size   = isset($params['size'])    ? $params['size']    : 'extrasmall';

    // language
    //$lang =  DataUtil::formatForOS(pnUserGetLang());

    $iconpath = 'images/icons/';

    // Include icon config file
    if (file_exists($iconpath . 'config.php') && !isset($icons)) {
        include($iconpath . 'config.php');
    }

    $size = DataUtil::formatForOS($size);
    $filename = DataUtil::formatForOS($icons[$params['type']]);

    $imgsrc = '';
    if (isset($icons[$params['type']])) {
        $imgpath = $iconpath.$size.'/'.$icons[$params['type']];
        if (is_readable($imgpath)) {
            $imgsrc = $imgpath;
        }
    }

    if ($imgsrc == '' && isset($params['default'])) {
        $imgsrc = $params['default'];
    }

    if ($imgsrc == '') {
        if (!isset($optional)) {
            $smarty->trigger_error(__f("%s: Image '%s' not found", array('pnicon', DataUtil::formatForDisplay($params['type']))));
        }
        return;
    }

    // If neither width nor height is set, get these parameters.
    // If one of them is set, we do NOT obtain the real dimensions.
    // This way it is easy to scale the image to a certain dimension.
    if (!isset($params['width']) && !isset($params['height'])) {
        if (!($_image_data = @getimagesize($imgsrc))) {
            $smarty->trigger_error(__f("%s: Image '%s' is not a valid image file", array('pnicon', DataUtil::formatForDisplay($params['type']))));
            return false;
        }
        $params['width']  = $_image_data[0];
        $params['height'] = $_image_data[1];
    }

    // unset all parameters which are no html argument from $params
    unset($params['type']);
    $assign = $params['assign'];
    unset($params['assign']);
    if (isset($params['altml'])) { // legacy
        unset($params['altml']);
    }
    if (isset($params['titleml'])) { // legacy
        unset($params['titleml']);
    }
    unset($params['optional']);
    unset($params['default']);
    unset($params['size']);

    $imgtag = '<img src="'.pnGetBaseURI().'/'.$imgsrc.'" ';
    foreach ($params as $key => $value) {
        $imgtag .= $key . '="' .$value  . '" ';
    }
    $imgtag .= ' />';

    if (isset($assign)) {
        $params['src'] = $imgsrc;
        $params['imgtag'] = $imgtag;
        $smarty->assign($assign, $params);
    } else {
        return $imgtag;
    }
}