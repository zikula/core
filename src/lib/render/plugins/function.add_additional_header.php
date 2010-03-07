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
 * Smarty function to add additional information to the <head> </head>
 * section of a Zikula document
 *
 * Available parameters:
 *   - header:   If set, the value is assigned to the global
 *               $additional_header array.  The value can be a single
 *               string or an array of strings.
 *
 * Example
 *   <!--[add_additional_header header='<title>This is the title</title>']-->
 *  OR
 *   <!--[add_additional_header header=$title]-->
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the value of the last status message posted, or void if no status message exists
 */
function smarty_function_add_additional_header($args, &$smarty)
{
    if (!isset($args['header'])) {
        return;
    }

    global $additional_header;

    if (is_array($args['header'])) {
        foreach($args['header'] as $header) {
            $additional_header[] = $header;
      }
    } else {
        $additional_header[] = $args['header'];
    }

    return;
}
