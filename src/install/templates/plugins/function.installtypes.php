<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display a drop down list of installation types (ie: essential vs. complete)
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - name:     Name for the control
 *   - selected: Selected value
 *
 * Example
 *   <!--[installtypes name=installtype]-->
 *
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the value of the last status message posted, or void if no status message exists
 */
function smarty_function_installtypes($params, &$smarty)
{
    if (!isset($params['name'])) {
        $smarty->trigger_error("installtypes: parameter 'name' required");
        return false;
    }

    if (!isset($params['all'])) {
        $all = true;
    }

    $installtypesdropdown = '<select name="'.DataUtil::formatforDisplay($params['name']).'">'."\n";

    $handle = opendir('install/installtypes/');
    $installtypes = array();
    while ($f = readdir($handle)) {
        if ($f != '.' && $f != '..' && $f != 'CVS' & $f != '.svn' && $f != 'index.html') {
            $f = str_replace('.php', '', $f);
            if($f == 'basic') {
                $installtypes["$f"] = __('Basic - only the modules required for Zikula to run are installed');
            }
        }
    }
    closedir($handle);
    foreach($installtypes as $installtype => $installlabel) {
        $installtypesdropdown .= '<option value="'.DataUtil::formatforDisplay($installtype).'">'.DataUtil::formatforDisplay($installlabel).'</option>'."\n";
    }
    $installtypesdropdown .= '<option value="complete">'.__('Complete - all modules with non-interactive installations found in the system are installed').'</option>'."\n";
    $installtypesdropdown .= '</select>'."\n";

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $installtypesdropdown);
    } else {
        return $installtypesdropdown;
    }
}
