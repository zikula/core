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
 * Zikula_View switch block.
 *
 * Available attributes:
 *  - expr (string|numeric) The variable to be tested against each of the
 *    {@link smarty_block_case() case} expressions.
 *
 * Example:
 * <pre>
 * {switch expr=$var}
 *   {case expr='1'}
 *     do some stuff for case $var == '1'
 *   {/case}
 *   {case expr='2'}
 *     do some stuff for case $var == '2'
 *   {/case}
 *   {case}
 *     default stuff
 *   {/case}
 * {/switch}
 * </pre>.
 *
 * @param array       $params  All attributes passed to this function from the template.
 * @param string      $content The content between the block tags.
 * @param Zikula_View $view    Reference to the {@link Zikula_View} object.
 * @param mixed       &$pages  Pages?.
 *
 * @see    smarty_block_case.
 *
 * @todo   Document the &$pages parameter, or correct it (possibly &$repeat?).
 *
 * @return string The content of the matching case.
 */
function smarty_block_switch($params, $content, Zikula_View $view, &$pages)
{
    if (is_null($content) && !array_key_exists('expr', $params)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_switch', 'expr')));
    }

    return $content;
}
