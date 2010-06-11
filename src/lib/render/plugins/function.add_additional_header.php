<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Add additional information to the <head>...</head> section of a Zikula document.
 *
 * Available attributes:
 *   - header (string|array) If set, the value is assigned to the global
 *                           <var>$additional_header array</var>.  The value can
 *                           be a single string or an array of strings.
 *
 * Examples:
 *
 * <samp>{add_additional_header header='<title>This is the title</title>'}</samp>
 *
 *  OR
 *
 * <samp>{add_additional_header header=$title}</samp>.
 *
 * @param array  $args    All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 *
 * @return void (The value is added to the <head> section of the document).
 */
function smarty_function_add_additional_header($args, &$smarty)
{
    if (!isset($args['header'])) {
        return;
    }

    global $additional_header;

    if (is_array($args['header'])) {
        foreach ($args['header'] as $header) {
            $additional_header[] = $header;
      }
    } else {
        $additional_header[] = $args['header'];
    }

    return;
}