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
 * Smarty switch block.
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
 * @param array  $params  All attributes passed to this function from the template.
 * @param string $content The content between the block tags.
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 * @param mixed  &$pages  Pages?.
 *
 * @author messju mohr <messju@lammfellpuschen.de>.
 * @author dasher <dasher@inspiredthinking.co.uk>.
 * @link   http://phpinsider.com/smarty-forum/viewtopic.php?t=11121.
 * @see    smarty_block_case.
 * @todo   Document the &$pages parameter, or correct it (possibly &$repeat?).
 * @return string The content of the matching case.
 */
function smarty_block_switch($params, $content, &$smarty, &$pages)
{
    if (is_null($content) && !array_key_exists('expr', $params)) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_switch', 'expr')));
    }

    return $content;
}
