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
 * Smarty function to display a drop down list of database engines
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - name:     Name for the control
 *   - selected: Selected value
 *
 * Example
 *   {dbtypes name='dbtype' selectedValue=$value}
 *
 *
 * @author       Mark West
 * @since        17 March 2006
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the value of the last status message posted, or void if no status message exists
 */
function smarty_function_dbtypes($params, &$smarty)
{
    if (!isset($params['name'])) {
        $smarty->trigger_error("dbtypes: parameter 'name' required");
        return false;
    }
    if (!isset($params['id'])) {
        $params['id'] = $params['name'];
    }

    $name = DataUtil::formatForDisplay($params['name']);
    $id = DataUtil::formatForDisplay($params['id']);
    $sv   = isset($params['selectedValue']) ? $params['selectedValue'] : 'mysql';

    $dbtypesdropdown = "<select name=\"$name\" id=\"$id\" onchange=\"dbtype_onchange()\">\n";
    if (function_exists('mysql_connect')) {
        $sel = $sv=='mysql' ? 'selected="selected"' : '';
        $dbtypesdropdown .= "<option value=\"mysql\" $sel>" . __('MySQL') . "</option>\n";
    }
    // disabled ref #2068
//    if (function_exists('mysqli_connect')) {
//        $sel = $sv=='mysqli' ? 'selected="selected"' : '';
//        $dbtypesdropdown .= "<option value=\"mysqli\" $sel>" . __('MySQL Improved') . "</option>\n";
//    }
    if (function_exists('mssql_connect')) {
        $sel = $sv=='mssql' ? 'selected="selected"' : '';
        $dbtypesdropdown .= "<option value=\"mssql\" $sel>" . __('MSSQL (alpha)') . "</option>\n";
    }
    if (function_exists('OCIPLogon')) {
        $sel = $sv=='oci8' ? 'selected="selected"' : '';
        $dbtypesdropdown .= "<option value=\"oci8\" $sel>" . __('Oracle (alpha) via OCI8 driver') . "</option>\n";
        $sel = $sv=='oracle' ? 'selected="selected"' : '';
        $dbtypesdropdown .= "<option value=\"oracle\" $sel>" . __('Oracle (alpha) via Oracle driver') . "</option>\n";
    }
    if (function_exists('pg_connect')) {
        $sel = $sv=='postgres' ? 'selected="selected"' : '';
        $dbtypesdropdown .= "<option value=\"postgres\" $sel>" . __('PostgreSQL') . "</option>\n";
    }
    $dbtypesdropdown .= "</select>\n";

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $dbtypesdropdown);
    } else {
        return $dbtypesdropdown;
    }
}
