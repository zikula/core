<?php
/**
 * Copyright Zikula Foundation 2004 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function that generates a flat lost of field names for hidden form
 * fields from a nested array set
 *
 * Available parameters:
 * - assign:  the results are assigned to the corresponding variable
 *
 * - prefix: (optional) needed for recursion
 *
 * - data: the data that should be stored in hidden fields (nested arrays allowed)
 *
 * Example
 *   {searchvartofieldnames assign='fnames'}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @return       boolean false on error
 */
function smarty_function_searchvartofieldnames($params, $smarty)
{
    $arr = array();
    if (isset($params['data']) && !empty($params['data'])) {
        $prefix = isset($params['prefix']) ? $params['prefix'] : '';
        if (is_array($params['data'])) {
            foreach ($params['data'] as $key => $data) {
                $tmp = smarty_function_searchvartofieldnames(array('data' => $data, 'prefix' => $prefix.'['.$key.']'), $smarty);
                $arr = array_merge($arr, $tmp);
            }
        } else {
            $arr[$prefix] = $params['data'];
        }
    }
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $arr);
    } else {
        return $arr;
    }
}
